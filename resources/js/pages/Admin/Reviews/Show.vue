<script setup lang="ts">
import ChangeReviewStatusController from '@/actions/App/Http/Controllers/Admin/ChangeReviewStatusController';
import ResolveReviewFlagsController from '@/actions/App/Http/Controllers/Admin/ResolveReviewFlagsController';
import PageHeader from '@/components/PageHeader.vue';
import SectionCard from '@/components/SectionCard.vue';
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
    <div class="space-y-6">
        <PageHeader
            :breadcrumbs="[{ title: 'Moderation', href: adminDashboard() }]"
            :title="`Review #${review.review.id}`"
            :description="`Currently ${review.statusLabel.toLowerCase()}`"
        />

        <SectionCard as="div">
            <ModerationReviewCard :item="review" />
        </SectionCard>

        <SectionCard
            title="Decide"
            description="Publishing keeps the review live; removing takes it off the site."
        >
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
        </SectionCard>

        <SectionCard title="Moderation history">
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
        </SectionCard>
    </div>
</template>
