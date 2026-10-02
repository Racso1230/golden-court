<script setup lang="ts">
import { Link, useForm } from '@inertiajs/vue3';
import DestroyReviewController from '@/actions/App/Http/Controllers/Reviews/DestroyReviewController';
import Heading from '@/components/Heading.vue';
import ReviewForm from '@/components/ReviewForm.vue';
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
    <div class="mx-auto flex w-full max-w-2xl flex-col space-y-6 p-4">
        <Link
            :href="courtShow({ venue: venueSlug, court: court.slug })"
            class="text-sm hover:underline"
        >
            ← {{ court.name }} at {{ venueName }}
        </Link>

        <Heading
            :title="`Edit your review of ${court.name}`"
            :description="venueName"
        />

        <ReviewForm :court-id="court.id" :review="review" />

        <section class="border-destructive/40 rounded-xl border p-4">
            <h2 class="font-semibold">Delete this review</h2>
            <p class="text-muted-foreground mt-1 text-sm">
                Removes your review from {{ court.name }}. You will not be able
                to review this court again.
            </p>
            <Dialog>
                <DialogTrigger as-child>
                    <Button type="button" variant="destructive" class="mt-3">
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
        </section>
    </div>
</template>
