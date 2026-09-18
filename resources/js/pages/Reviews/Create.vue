<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import StoreReviewController from '@/actions/App/Http/Controllers/Reviews/StoreReviewController';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

// Minimal shape for this phase's form; the generated Data types arrive in Phase 7.
type CourtSummary = {
    id: number;
    name: string;
    venue: { name: string; city: string };
};

defineProps<{ court: CourtSummary }>();

const dimensions = [
    { name: 'glass', label: 'Glass', hint: 'Clarity, cleanliness, no cracks' },
    {
        name: 'lighting',
        label: 'Lighting',
        hint: 'Even and bright enough to play at night',
    },
    { name: 'turf', label: 'Turf', hint: 'Condition of the surface and sand' },
    {
        name: 'facilities',
        label: 'Facilities',
        hint: 'Changing rooms, showers, seating',
    },
] as const;

const ratingValues = [1, 2, 3, 4, 5];

const today = new Date().toISOString().slice(0, 10);
</script>

<template>
    <Head :title="`Review ${court.name}`" />

    <div class="mx-auto flex w-full max-w-2xl flex-col space-y-6 p-4">
        <Heading
            :title="`Review ${court.name}`"
            :description="`${court.venue.name}, ${court.venue.city}`"
        />

        <Form
            v-bind="StoreReviewController.form()"
            class="space-y-8"
            v-slot="{ errors, processing }"
        >
            <input type="hidden" name="court_id" :value="court.id" />
            <InputError :message="errors.court_id" />

            <fieldset
                v-for="dimension in dimensions"
                :key="dimension.name"
                class="grid gap-2"
            >
                <legend class="text-sm font-medium">
                    {{ dimension.label }}
                </legend>
                <p class="text-muted-foreground text-xs">
                    {{ dimension.hint }}
                </p>
                <div class="flex gap-4">
                    <label
                        v-for="value in ratingValues"
                        :key="value"
                        class="flex cursor-pointer items-center gap-1 text-sm"
                    >
                        <input
                            type="radio"
                            :name="dimension.name"
                            :value="value"
                            required
                            class="accent-primary"
                        />
                        {{ value }}
                    </label>
                </div>
                <InputError :message="errors[dimension.name]" />
            </fieldset>

            <div class="grid gap-2">
                <Label for="body">Your review</Label>
                <textarea
                    id="body"
                    name="body"
                    required
                    minlength="20"
                    maxlength="2000"
                    rows="6"
                    class="border-input bg-background placeholder:text-muted-foreground focus-visible:ring-ring w-full rounded-md border px-3 py-2 text-sm shadow-xs focus-visible:ring-1 focus-visible:outline-hidden"
                    placeholder="At least 20 characters. What was it like to play here?"
                />
                <InputError :message="errors.body" />
            </div>

            <div class="grid gap-2">
                <Label for="played_on">When did you play? (optional)</Label>
                <Input
                    id="played_on"
                    type="date"
                    name="played_on"
                    :max="today"
                    class="w-fit"
                />
                <InputError :message="errors.played_on" />
            </div>

            <div class="flex items-center gap-4">
                <Button :disabled="processing" data-test="submit-review-button">
                    Submit review
                </Button>
            </div>
        </Form>
    </div>
</template>
