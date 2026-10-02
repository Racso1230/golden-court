<script setup lang="ts">
import ResolveReviewFlagsController from '@/actions/App/Http/Controllers/Admin/ResolveReviewFlagsController';
import Heading from '@/components/Heading.vue';
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
    <div class="flex flex-col space-y-6 p-4">
        <Heading
            title="Reviews with open flags"
            :description="`${reviews.total} awaiting a decision`"
        />

        <p v-if="reviews.data.length === 0" class="text-muted-foreground">
            Nothing to review.
        </p>

        <Table v-else>
            <TableHeader>
                <TableRow>
                    <TableHead>Review</TableHead>
                    <TableHead class="text-right">Open flags</TableHead>
                    <TableHead><span class="sr-only">Actions</span></TableHead>
                </TableRow>
            </TableHeader>
            <TableBody>
                <TableRow v-for="item in reviews.data" :key="item.review.id">
                    <TableCell class="max-w-2xl">
                        <ModerationReviewCard :item="item" show-detail-link />
                    </TableCell>
                    <TableCell class="text-right font-semibold tabular-nums">
                        {{ item.unresolvedFlagCount }}
                    </TableCell>
                    <TableCell>
                        <ReviewOutcomeButtons
                            :action="
                                ResolveReviewFlagsController.url(item.review.id)
                            "
                            publish-label="Dismiss flags"
                            remove-label="Remove"
                        />
                    </TableCell>
                </TableRow>
            </TableBody>
        </Table>

        <PaginationLinks :links="reviews.links" />
    </div>
</template>
