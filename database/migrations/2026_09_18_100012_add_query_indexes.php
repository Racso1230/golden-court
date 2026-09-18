<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Index audit. PostgreSQL does not index foreign keys automatically, and the
 * query objects filter and sort on a few columns the original tables did not
 * cover. Each index below maps to a specific query:
 *
 * - reviews (status, created_at): PendingReviewsQuery.
 * - review_replies (user_id): a venue owner's replies.
 * - review_flags partial on review_id WHERE resolved_at IS NULL: unresolved
 *   flag counts in FlagReviewAction, FlaggedReviewsQuery, ModerationCountsQuery.
 * - venue_claims (user_id, created_at): UserClaimsQuery.
 * - venue_claims (status, created_at): PendingClaimsQuery.
 * - venues (claimed_by_user_id): "venues I own" lookups and the FK.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reviews', function (Blueprint $table): void {
            $table->index(['status', 'created_at']);
        });

        Schema::table('review_replies', function (Blueprint $table): void {
            $table->index('user_id');
        });

        DB::statement('CREATE INDEX review_flags_unresolved_index ON review_flags (review_id) WHERE resolved_at IS NULL');

        Schema::table('venue_claims', function (Blueprint $table): void {
            $table->index(['user_id', 'created_at']);
            $table->index(['status', 'created_at']);
        });

        Schema::table('venues', function (Blueprint $table): void {
            $table->index('claimed_by_user_id');
        });
    }

    public function down(): void
    {
        Schema::table('reviews', function (Blueprint $table): void {
            $table->dropIndex(['status', 'created_at']);
        });

        Schema::table('review_replies', function (Blueprint $table): void {
            $table->dropIndex(['user_id']);
        });

        DB::statement('DROP INDEX IF EXISTS review_flags_unresolved_index');

        Schema::table('venue_claims', function (Blueprint $table): void {
            $table->dropIndex(['user_id', 'created_at']);
            $table->dropIndex(['status', 'created_at']);
        });

        Schema::table('venues', function (Blueprint $table): void {
            $table->dropIndex(['claimed_by_user_id']);
        });
    }
};
