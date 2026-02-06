<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('booking_locations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained('bookings')->onDelete('cascade');
            $table->string('address');
            $table->string('barangay')->nullable();
            $table->string('city');
            $table->string('province');
            $table->string('postal_code', 10)->nullable();
            $table->decimal('latitude', 10, 7);
            $table->decimal('longitude', 10, 7);
            $table->string('landmark')->nullable();
            $table->text('delivery_instructions')->nullable();
            $table->timestamps();
            $table->index('booking_id');
        });

        try {
            DB::statement("ALTER TABLE booking_locations ADD COLUMN IF NOT EXISTS location GEOGRAPHY(POINT,4326);");
            DB::statement("CREATE INDEX IF NOT EXISTS booking_locations_location_idx ON booking_locations USING GIST (location);");

            DB::statement(<<<'SQL'
            CREATE OR REPLACE FUNCTION update_booking_location()
            RETURNS TRIGGER AS $$
            BEGIN
                NEW.location := ST_SetSRID(ST_MakePoint(NEW.longitude, NEW.latitude), 4326)::geography;
                RETURN NEW;
            END;
            $$ LANGUAGE plpgsql;
            SQL
            );

            DB::statement("CREATE TRIGGER booking_locations_location_trigger BEFORE INSERT OR UPDATE ON booking_locations FOR EACH ROW EXECUTE FUNCTION update_booking_location();");
        } catch (\Throwable $e) {
            // Skip PostGIS-specific steps if not available
        }
    }

    public function down(): void
    {
        try {
            DB::statement("DROP TRIGGER IF EXISTS booking_locations_location_trigger ON booking_locations;");
            DB::statement("DROP FUNCTION IF EXISTS update_booking_location();");
        } catch (\Throwable $e) {
        }

        Schema::dropIfExists('booking_locations');
    }
};
