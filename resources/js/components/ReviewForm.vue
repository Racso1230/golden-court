<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { onMounted, ref } from 'vue';
import StoreReviewController from '@/actions/App/Http/Controllers/Reviews/StoreReviewController';
import UpdateReviewController from '@/actions/App/Http/Controllers/Reviews/UpdateReviewController';
import InputError from '@/components/InputError.vue';
import RatingInput from '@/components/RatingInput.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    isRating,
    type Rating,
    type Review,
    type ReviewFormData,
} from '@/types';

/**
 * Shared by the create and edit pages. In edit mode the existing review's
 * scores, body and date pre-fill the form and the PATCH route is used.
 */
const props = defineProps<{
    courtId: number;
    review?: Review;
}>();

const dimensions: {
    key: keyof Pick<
        ReviewFormData,
        'glass' | 'lighting' | 'turf' | 'facilities'
    >;
    label: string;
    hint: string;
}[] = [
    { key: 'glass', label: 'Glass', hint: 'Clarity, cleanliness, no cracks' },
    {
        key: 'lighting',
        label: 'Lighting',
        hint: 'Even and bright enough to play at night',
    },
    { key: 'turf', label: 'Turf', hint: 'Condition of the surface and sand' },
    {
        key: 'facilities',
        label: 'Facilities',
        hint: 'Changing rooms, showers, seating',
    },
];

function initialRating(value: number | undefined): Rating | null {
    return isRating(value) ? value : null;
}

const form = useForm<ReviewFormData>({
    court_id: props.courtId,
    glass: initialRating(props.review?.scores.glass),
    lighting: initialRating(props.review?.scores.lighting),
    turf: initialRating(props.review?.scores.turf),
    facilities: initialRating(props.review?.scores.facilities),
    body: props.review?.body ?? '',
    played_on: props.review?.playedOn?.slice(0, 10) ?? '',
});

const today = ref('');

onMounted(() => {
    // Browser only, so the server never bakes its own clock into the markup.
    today.value = new Date().toISOString().slice(0, 10);
});

function submit(): void {
    if (props.review) {
        form.patch(UpdateReviewController.url(props.review.id));
    } else {
        form.post(StoreReviewController.url());
    }
}
</script>

<template>
    <form class="space-y-8" @submit.prevent="submit">
        <InputError :message="form.errors.court_id" />

        <RatingInput
            v-for="dimension in dimensions"
            :key="dimension.key"
            v-model="form[dimension.key]"
            :label="dimension.label"
            :hint="dimension.hint"
            :error="form.errors[dimension.key]"
        />

        <div class="grid gap-1.5">
            <Label for="body">Your review</Label>
            <textarea
                id="body"
                v-model="form.body"
                required
                minlength="20"
                maxlength="2000"
                rows="6"
                class="border-input bg-background placeholder:text-muted-foreground focus-visible:ring-ring/50 w-full rounded-md border px-3 py-2 text-sm shadow-xs outline-none focus-visible:ring-[3px]"
                placeholder="At least 20 characters. What was it like to play here?"
                :aria-invalid="form.errors.body ? 'true' : undefined"
            />
            <p class="text-muted-foreground text-xs tabular-nums">
                {{ form.body.length }} / 2000
            </p>
            <InputError :message="form.errors.body" />
        </div>

        <div class="grid gap-1.5">
            <Label for="played_on">When did you play? (optional)</Label>
            <Input
                id="played_on"
                v-model="form.played_on"
                type="date"
                :max="today"
                class="w-fit"
            />
            <InputError :message="form.errors.played_on" />
        </div>

        <div class="flex items-center gap-4">
            <Button type="submit" :disabled="form.processing">
                {{ review ? 'Save changes' : 'Submit review' }}
            </Button>
            <p
                v-if="form.recentlySuccessful"
                class="text-muted-foreground text-sm"
                role="status"
            >
                Saved.
            </p>
        </div>
    </form>
</template>
