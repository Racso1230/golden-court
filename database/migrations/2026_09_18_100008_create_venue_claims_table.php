<?php

declare(strict_types=1);

use App\Domain\Claims\Enums\ClaimStatus;
use App\Support\Database\CheckConstraint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('venue_claims', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('venue_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('status')->default(ClaimStatus::Pending->value);
            $table->text('evidence');
            $table->foreignId('reviewed_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->timestamps();
        });

        CheckConstraint::enum('venue_claims', 'status', ClaimStatus::class);

        // A venue can have any number of settled claims but only one awaiting review.
        DB::statement(sprintf(
            "CREATE UNIQUE INDEX venue_claims_single_pending_per_venue ON venue_claims (venue_id) WHERE status = '%s'",
            ClaimStatus::Pending->value,
        ));
    }

    public function down(): void
    {
        Schema::dropIfExists('venue_claims');
    }
};
