<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import DestroyReviewController from '@/actions/App/Http/Controllers/Reviews/DestroyReviewController';
import PageHeader from '@/components/PageHeader.vue';
import ReviewForm from '@/components/ReviewForm.vue';
import SectionCard from '@/components/SectionCard.vue';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import { show as courtShow } from '@/routes/courts';
import { index as venuesIndex, show as venueShow } from '@/routes/venues';
import type { CourtSummary, Review } from '@/types';

const props = defineProps<{
    review: Review;
    court: CourtSummary;
    venueName: string;
    venueSlug: string;
}>();

const deleteForm = useForm({});

function destroy(): void {
    deleteForm.delete(DestroyReviewController.url(props.review.id));
}
</script>

<template>
    <div class="mx-auto max-w-3xl space-y-6">
        <PageHeader
            :title="`Edit your review of ${court.name}`"
            :description="venueName"
            :breadcrumbs="[
                { title: 'Venues', href: venuesIndex() },
                { title: venueName, href: venueShow(venueSlug) },
                {
                    title: court.name,
                    href: courtShow({ venue: venueSlug, court: court.slug }),
                },
            ]"
        />

        <ReviewForm :court-id="court.id" :review="review" />

        <SectionCard
            title="Delete this review"
            description="Removes your review. You will not be able to review this court again."
            class="border-destructive/30"
        >
            <Dialog>
                <DialogTrigger as-child>
                    <Button type="button" variant="destructive">
                        Delete review
                    </Button>
                </DialogTrigger>
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>Delete your review?</DialogTitle>
                        <DialogDescription>
                            This cannot be undone and the court's score will be
                            recalculated without it.
                        </DialogDescription>
                    </DialogHeader>
                    <DialogFooter>
                        <Button
                            type="button"
                            variant="destructive"
                            :disabled="deleteForm.processing"
                            @click="destroy"
                        >
                            Delete review
                        </Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>
        </SectionCard>
    </div>
</template>
