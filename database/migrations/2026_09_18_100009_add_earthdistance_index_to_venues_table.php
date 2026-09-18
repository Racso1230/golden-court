<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Proximity search uses the contrib `earthdistance` extension (on top of
 * `cube`) rather than PostGIS: it ships with every PostgreSQL build, needs no
 * separate image in CI, and great-circle distance is all the product needs.
 * The functional GiST index lets `earth_box(...) @> ll_to_earth(...)` prune
 * candidates before the exact `earth_distance` check.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::statement('CREATE EXTENSION IF NOT EXISTS cube');
        DB::statement('CREATE EXTENSION IF NOT EXISTS earthdistance');

        DB::statement(<<<'SQL'
            CREATE INDEX venues_location_earth_index
            ON venues
            USING GIST (ll_to_earth(latitude::double precision, longitude::double precision))
        SQL);
    }

    public function down(): void
    {
        DB::statement('DROP INDEX IF EXISTS venues_location_earth_index');
    }
};
