<?php

declare(strict_types=1);

use App\Support\Database\CheckConstraint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('venues', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();

            $table->string('address_line_1');
            $table->string('address_line_2')->nullable();
            $table->string('city');
            $table->string('postcode');
            $table->char('country_code', 2)->default('GB');

            $table->decimal('latitude', 9, 6);
            $table->decimal('longitude', 9, 6);

            $table->string('website')->nullable();
            $table->string('phone')->nullable();

            // Denormalised; written only by the aggregate recalculation job.
            $table->decimal('aggregate_score', 2, 1)->default(0.0);
            $table->unsignedInteger('review_count')->default(0);

            $table->foreignId('claimed_by_user_id')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();

            $table->index('city');
            $table->index(['latitude', 'longitude']);
        });

        CheckConstraint::add('venues', 'venues_latitude_check', 'latitude BETWEEN -90 AND 90');
        CheckConstraint::add('venues', 'venues_longitude_check', 'longitude BETWEEN -180 AND 180');
        CheckConstraint::add('venues', 'venues_aggregate_score_check', 'aggregate_score BETWEEN 0 AND 5');
        CheckConstraint::add('venues', 'venues_review_count_check', 'review_count >= 0');

        // Full-text search over name and city, maintained by PostgreSQL itself.
        DB::statement(<<<'SQL'
            ALTER TABLE venues
            ADD COLUMN search_vector tsvector
            GENERATED ALWAYS AS (to_tsvector('english', name || ' ' || city)) STORED
        SQL);

        DB::statement('CREATE INDEX venues_search_vector_index ON venues USING GIN (search_vector)');
    }

    public function down(): void
    {
        Schema::dropIfExists('venues');
    }
};
