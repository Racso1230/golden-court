<?php

declare(strict_types=1);

use Illuminate\Support\Facades\DB;

/**
 * @return array<int, string>
 */
function indexDefinitions(string $table): array
{
    return DB::table('pg_indexes')
        ->where('schemaname', 'public')
        ->where('tablename', $table)
        ->pluck('indexdef')
        ->map(fn (mixed $definition): string => (string) $definition)
        ->all();
}

it('indexes the columns the query objects filter and sort on', function (string $table, string $needle): void {
    expect(implode("\n", indexDefinitions($table)))->toContain($needle);
})->with([
    'pending reviews' => ['reviews', '(status, created_at)'],
    'court reviews by status' => ['reviews', '(court_id, status)'],
    'review replies by owner' => ['review_replies', '(user_id)'],
    'unresolved flags' => ['review_flags', 'WHERE (resolved_at IS NULL)'],
    'claims by user' => ['venue_claims', '(user_id, created_at)'],
    'pending claims' => ['venue_claims', '(status, created_at)'],
    'venue owner' => ['venues', '(claimed_by_user_id)'],
    'venue search vector' => ['venues', 'USING gin (search_vector)'],
    'venue location' => ['venues', 'USING gist (ll_to_earth'],
    'moderation log subject' => ['moderation_logs', '(subject_type, subject_id)'],
]);
