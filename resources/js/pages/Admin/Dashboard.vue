<script setup lang="ts">
import { Building2, CircleCheck, Clock, Flag } from '@lucide/vue';
import { computed } from 'vue';
import PageHeader from '@/components/PageHeader.vue';
import StatCard from '@/components/StatCard.vue';
import { index as claimsIndex } from '@/routes/admin/claims';
import { index as flagsIndex } from '@/routes/admin/flags';
import { pending as pendingReviews } from '@/routes/admin/reviews';
import type { ModerationCounts } from '@/types';

const props = defineProps<{ counts: ModerationCounts }>();

const allClear = computed(
    () =>
        props.counts.pendingClaims === 0 &&
        props.counts.flaggedReviews === 0 &&
        props.counts.pendingReviews === 0,
);
</script>

<template>
    <PageHeader
        eyebrow="Admin"
        title="Moderation"
        description="What needs a decision right now."
    />

    <ul class="grid gap-4 sm:grid-cols-3">
        <li>
            <StatCard
                label="Pending claims"
                :value="counts.pendingClaims"
                hint="Venue ownership requests"
                :href="claimsIndex()"
            >
                <template #icon><Building2 /></template>
            </StatCard>
        </li>
        <li>
            <StatCard
                label="Reviews with open flags"
                :value="counts.flaggedReviews"
                hint="Reported by players"
                :href="flagsIndex()"
            >
                <template #icon><Flag /></template>
            </StatCard>
        </li>
        <li>
            <StatCard
                label="Pending reviews"
                :value="counts.pendingReviews"
                hint="Not yet published"
                :href="pendingReviews()"
            >
                <template #icon><Clock /></template>
            </StatCard>
        </li>
    </ul>

    <p
        v-if="allClear"
        class="text-success mt-6 flex items-center gap-2 text-sm"
        role="status"
    >
        <CircleCheck class="size-4" aria-hidden="true" />
        Everything is clear. Nice work.
    </p>
</template>
