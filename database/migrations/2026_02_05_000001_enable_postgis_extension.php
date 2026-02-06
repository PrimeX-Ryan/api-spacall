<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Enable PostGIS extensions if they do not already exist
        DB::statement('CREATE EXTENSION IF NOT EXISTS postgis;');
        DB::statement('CREATE EXTENSION IF NOT EXISTS postgis_topology;');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Note: Dropping extensions in down() may affect other objects depending on PostGIS.
        DB::statement('DROP EXTENSION IF EXISTS postgis_topology;');
        DB::statement('DROP EXTENSION IF EXISTS postgis;');
    }
};
