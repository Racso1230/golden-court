<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import ChangeReviewStatusController from '@/actions/App/Http/Controllers/Admin/ChangeReviewStatusController';
import ResolveReviewFlagsController from '@/actions/App/Http/Controllers/Admin/ResolveReviewFlagsController';
import Heading from '@/components/Heading.vue';
import ModerationReviewCard from '@/components/ModerationReviewCard.vue';
import ReviewOutcomeButtons from '@/components/ReviewOutcomeButtons.vue';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { formatDateTime } from '@/lib/dates';
import { dashboard as adminDashboard } from '@/routes/admin';
import type { ModerationLogEntry, ModerationReview } from '@/types';

defineProps<{ review: ModerationReview; log: ModerationLogEntry[] }>();

function describeDetails(details: ModerationLogEntry['details']): string {
    return Object.entries(details)
        .map(([key, value]) => `${key}: ${String(value)}`)
        .join(', ');
}
</script>

<template>
    <div class="flex flex-col space-y-6 p-4">
        <Link :href="adminDashboard()" class="text-sm hover:underline">
            ← Moderation
        </Link>

        <Heading
            :title="`Review #${review.review.id}`"
            :description="`Currently ${review.statusLabel.toLowerCase()}`"
        />

        <div class="rounded-xl border p-4">
            <ModerationReviewCard :item="review" />
        </div>

        <section class="space-y-2" aria-labelledby="decide-heading">
            <h2 id="decide-heading" class="font-semibold">Decide</h2>
            <ReviewOutcomeButtons
                v-if="review.unresolvedFlagCount > 0"
                :action="ResolveReviewFlagsController.url(review.review.id)"
                publish-label="Dismiss flags, keep live"
                remove-label="Remove review"
            />
            <ReviewOutcomeButtons
                v-else
                :action="ChangeReviewStatusController.url(review.review.id)"
            />
        </section>

        <section class="space-y-2" aria-labelledby="history-heading">
            <h2 id="history-heading" class="font-semibold">
                Moderation history
            </h2>
            <p v-if="log.length === 0" class="text-muted-foreground text-sm">
                No moderation actions recorded yet.
            </p>
            <Table v-else>
                <TableHeader>
                    <TableRow>
                        <TableHead>When</TableHead>
                        <TableHead>Action</TableHead>
                        <TableHead>By</TableHead>
                        <TableHead>Details</TableHead>
                    </TableRow>
                </TableHeader>
                <TableBody>
                    <TableRow v-for="entry in log" :key="entry.id">
                        <TableCell class="whitespace-nowrap tabular-nums">
                            {{ formatDateTime(entry.createdAt) }}
                        </TableCell>
                        <TableCell class="font-medium">
                            {{ entry.actionLabel }}
                        </TableCell>
                        <TableCell>
                            {{ entry.actorDisplayName ?? entry.actorLabel }}
                        </TableCell>
                        <TableCell class="text-muted-foreground">
                            {{ describeDetails(entry.details) }}
                        </TableCell>
                    </TableRow>
                </TableBody>
            </Table>
        </section>
    </div>
</template>
