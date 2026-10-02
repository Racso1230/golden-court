<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import Heading from '@/components/Heading.vue';
import ReviewForm from '@/components/ReviewForm.vue';
import { show as courtShow } from '@/routes/courts';
import type { CourtSummary } from '@/types';

defineProps<{
    court: CourtSummary;
    venueName: string;
    venueSlug: string;
}>();
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
            :title="`Review ${court.name}`"
            :description="`${venueName} · ${court.courtTypeLabel} · ${court.wallTypeLabel} · ${court.surfaceLabel}`"
        />

        <ReviewForm :court-id="court.id" />
    </div>
</template>
