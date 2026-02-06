<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('user_addresses', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('label')->nullable();
            $table->string('street_address');
            $table->string('city')->nullable();
            $table->string('province')->nullable();
            $table->string('country')->nullable();
            $table->double('latitude', 10, 7)->nullable();
            $table->double('longitude', 10, 7)->nullable();
            $table->boolean('is_default')->default(false);
            $table->timestamps();
        });

        // Add PostGIS geography column for spatial queries (requires PostGIS)
        DB::statement("ALTER TABLE user_addresses ADD COLUMN IF NOT EXISTS location geography(Point,4326);");
        DB::statement("CREATE INDEX IF NOT EXISTS user_addresses_location_gist_idx ON user_addresses USING GIST (location);");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        DB::statement('DROP INDEX IF EXISTS user_addresses_location_gist_idx');
        Schema::dropIfExists('user_addresses');
    }
};
