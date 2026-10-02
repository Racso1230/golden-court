<script setup lang="ts">
import ChangeReviewStatusController from '@/actions/App/Http/Controllers/Admin/ChangeReviewStatusController';
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
            title="Pending reviews"
            :description="`${reviews.total} waiting to go live`"
        />

        <p v-if="reviews.data.length === 0" class="text-muted-foreground">
            Nothing to review.
        </p>

        <Table v-else>
            <TableHeader>
                <TableRow>
                    <TableHead>Review</TableHead>
                    <TableHead><span class="sr-only">Actions</span></TableHead>
                </TableRow>
            </TableHeader>
            <TableBody>
                <TableRow v-for="item in reviews.data" :key="item.review.id">
                    <TableCell class="max-w-2xl">
                        <ModerationReviewCard :item="item" show-detail-link />
                    </TableCell>
                    <TableCell>
                        <ReviewOutcomeButtons
                            :action="
                                ChangeReviewStatusController.url(item.review.id)
                            "
                        />
                    </TableCell>
                </TableRow>
            </TableBody>
        </Table>

        <PaginationLinks :links="reviews.links" />
    </div>
</template>
