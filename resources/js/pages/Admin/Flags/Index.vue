<script setup lang="ts">
import ResolveReviewFlagsController from '@/actions/App/Http/Controllers/Admin/ResolveReviewFlagsController';
import { ShieldCheck } from '@lucide/vue';
import EmptyState from '@/components/EmptyState.vue';
import PageHeader from '@/components/PageHeader.vue';
import ModerationReviewCard from '@/components/ModerationReviewCard.vue';
import PaginationLinks from '@/components/PaginationLinks.vue';
import ReviewOutcomeButtons from '@/components/ReviewOutcomeButtons.vue';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import type { ModerationReview, Paginated } from '@/types';

defineProps<{ reviews: Paginated<ModerationReview> }>();
</script>

<template>
    <div class="space-y-6">
        <PageHeader
            eyebrow="Admin"
            title="Reviews with open flags"
            :description="`${reviews.total} awaiting a decision`"
        />

        <EmptyState
            v-if="reviews.data.length === 0"
            title="Nothing to review"
            description="The queue is clear. New items appear here as players submit them."
        >
            <template #icon><ShieldCheck /></template>
        </EmptyState>

        <div v-else class="bg-card overflow-hidden rounded-xl border shadow-xs">
            <Table>
                <TableHeader class="bg-muted/50">
                    <TableRow>
                        <TableHead class="px-5">Review</TableHead>
                        <TableHead class="text-right">Open flags</TableHead>
                        <TableHead
                            ><span class="sr-only">Actions</span></TableHead
                        >
                    </TableRow>
                </TableHeader>
                <TableBody>
                    <TableRow
                        v-for="item in reviews.data"
                        :key="item.review.id"
                    >
                        <TableCell
                            class="max-w-2xl px-5 py-4 whitespace-normal"
                        >
                            <ModerationReviewCard
                                :item="item"
                                show-detail-link
                            />
                        </TableCell>
                        <TableCell
                            class="text-right font-semibold tabular-nums"
                        >
                            {{ item.unresolvedFlagCount }}
                        </TableCell>
                        <TableCell>
                            <ReviewOutcomeButtons
                                :action="
                                    ResolveReviewFlagsController.url(
                                        item.review.id,
                                    )
                                "
                                publish-label="Dismiss flags"
                                remove-label="Remove"
                            />
                        </TableCell>
                    </TableRow>
                </TableBody>
            </Table>
        </div>

        <PaginationLinks :links="reviews.links" />
    </div>
</template>
