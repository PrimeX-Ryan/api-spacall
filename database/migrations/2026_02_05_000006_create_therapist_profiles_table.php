<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('therapist_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('provider_id')->constrained()->onDelete('cascade');

            $table->string('specialization')->nullable();
            $table->text('bio')->nullable();
            $table->unsignedInteger('years_of_experience')->nullable();
            $table->jsonb('certifications')->nullable();

            $table->unsignedInteger('service_radius_km')->default(5);
            $table->decimal('base_latitude', 10, 7)->nullable();
            $table->decimal('base_longitude', 10, 7)->nullable();
            $table->string('base_address')->nullable();

            $table->jsonb('available_days')->nullable();
            $table->time('available_from')->nullable();
            $table->time('available_until')->nullable();

            $table->timestamps();

            $table->index('provider_id');
        });

        DB::statement("ALTER TABLE therapist_profiles ADD COLUMN IF NOT EXISTS base_location GEOGRAPHY(POINT,4326);");

        DB::statement(<<<'SQL'
CREATE OR REPLACE FUNCTION update_therapist_base_location()
RETURNS TRIGGER AS $$
BEGIN
    IF NEW.base_latitude IS NOT NULL AND NEW.base_longitude IS NOT NULL THEN
        NEW.base_location = ST_SetSRID(ST_MakePoint(NEW.base_longitude, NEW.base_latitude), 4326)::geography;
    END IF;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;
SQL
        );

        DB::statement("CREATE TRIGGER therapist_profiles_location_trigger BEFORE INSERT OR UPDATE ON therapist_profiles FOR EACH ROW EXECUTE FUNCTION update_therapist_base_location();");
        DB::statement("CREATE INDEX IF NOT EXISTS therapist_profiles_base_location_idx ON therapist_profiles USING GIST (base_location);");
    }

    public function down(): void
    {
        DB::statement("DROP TRIGGER IF EXISTS therapist_profiles_location_trigger ON therapist_profiles;");
        DB::statement("DROP FUNCTION IF EXISTS update_therapist_base_location();");
        Schema::dropIfExists('therapist_profiles');
    }
};
