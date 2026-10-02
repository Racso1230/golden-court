<script setup lang="ts">
import PageHeader from '@/components/PageHeader.vue';
import ReviewForm from '@/components/ReviewForm.vue';
import { show as courtShow } from '@/routes/courts';
import { index as venuesIndex, show as venueShow } from '@/routes/venues';
import type { CourtSummary } from '@/types';

defineProps<{
    court: CourtSummary;
    venueName: string;
    venueSlug: string;
}>();
</script>

<template>
    <div class="mx-auto max-w-3xl">
        <PageHeader
            :title="`Review ${court.name}`"
            :description="`${venueName} · ${court.courtTypeLabel} · ${court.wallTypeLabel} walls · ${court.surfaceLabel}`"
            :breadcrumbs="[
                { title: 'Venues', href: venuesIndex() },
                { title: venueName, href: venueShow(venueSlug) },
                {
                    title: court.name,
                    href: courtShow({ venue: venueSlug, court: court.slug }),
                },
            ]"
        />

        <ReviewForm :court-id="court.id" />
    </div>
</template>
