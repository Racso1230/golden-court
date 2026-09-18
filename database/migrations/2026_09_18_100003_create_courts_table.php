<?php

declare(strict_types=1);

use App\Domain\Courts\Enums\CourtType;
use App\Domain\Courts\Enums\Surface;
use App\Domain\Courts\Enums\WallType;
use App\Support\Database\CheckConstraint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('courts', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('venue_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('slug');

            $table->string('court_type');
            $table->string('wall_type');
            $table->string('surface');

            // Denormalised; written only by the aggregate recalculation job.
            $table->decimal('aggregate_score', 2, 1)->default(0.0);
            $table->unsignedInteger('review_count')->default(0);

            $table->timestamps();
            $table->softDeletes();

            $table->unique(['venue_id', 'slug']);
        });

        CheckConstraint::enum('courts', 'court_type', CourtType::class);
        CheckConstraint::enum('courts', 'wall_type', WallType::class);
        CheckConstraint::enum('courts', 'surface', Surface::class);
        CheckConstraint::add('courts', 'courts_aggregate_score_check', 'aggregate_score BETWEEN 0 AND 5');
        CheckConstraint::add('courts', 'courts_review_count_check', 'review_count >= 0');
    }

    public function down(): void
    {
        Schema::dropIfExists('courts');
    }
};
