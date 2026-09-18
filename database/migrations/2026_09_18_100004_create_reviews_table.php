<?php

declare(strict_types=1);

use App\Domain\Reviews\Enums\ReviewStatus;
use App\Domain\Reviews\ValueObjects\Rating;
use App\Support\Database\CheckConstraint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const int BODY_MIN_LENGTH = 20;

    private const int BODY_MAX_LENGTH = 2000;

    public function up(): void
    {
        Schema::create('reviews', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('court_id')->constrained()->cascadeOnDelete();

            $table->smallInteger('glass_rating');
            $table->smallInteger('lighting_rating');
            $table->smallInteger('turf_rating');
            $table->smallInteger('facilities_rating');

            $table->text('body');
            $table->string('status')->default(ReviewStatus::Pending->value);
            $table->date('played_on')->nullable();

            // Denormalised; maintained by the voting actions.
            $table->unsignedInteger('helpful_count')->default(0);

            $table->timestamps();
            $table->softDeletes();

            $table->unique(['user_id', 'court_id']);
            $table->index(['court_id', 'status']);
            $table->index('created_at');
        });

        foreach (['glass_rating', 'lighting_rating', 'turf_rating', 'facilities_rating'] as $column) {
            CheckConstraint::add(
                'reviews',
                sprintf('reviews_%s_check', $column),
                sprintf('%s BETWEEN %d AND %d', $column, Rating::MIN, Rating::MAX),
            );
        }

        CheckConstraint::add(
            'reviews',
            'reviews_body_length_check',
            sprintf('char_length(body) BETWEEN %d AND %d', self::BODY_MIN_LENGTH, self::BODY_MAX_LENGTH),
        );

        CheckConstraint::enum('reviews', 'status', ReviewStatus::class);
    }

    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};
