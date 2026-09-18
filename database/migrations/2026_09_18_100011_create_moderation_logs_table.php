<?php

declare(strict_types=1);

use App\Domain\Moderation\Enums\ModerationAction;
use App\Domain\Moderation\Enums\ModerationSubject;
use App\Support\Database\CheckConstraint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Append-only record of moderation decisions. Rows are never updated or
 * deleted by the application, so there is no updated_at.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('moderation_logs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('actor_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('actor_label');
            $table->string('action');
            $table->string('subject_type');
            $table->unsignedBigInteger('subject_id');
            $table->jsonb('details')->nullable();
            $table->timestamp('created_at');

            $table->index(['subject_type', 'subject_id']);
            $table->index('created_at');
        });

        CheckConstraint::enum('moderation_logs', 'action', ModerationAction::class);
        CheckConstraint::enum('moderation_logs', 'subject_type', ModerationSubject::class);
    }

    public function down(): void
    {
        Schema::dropIfExists('moderation_logs');
    }
};
