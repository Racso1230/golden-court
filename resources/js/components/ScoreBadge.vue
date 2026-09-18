<script setup lang="ts">
import RatingStars from '@/components/RatingStars.vue';

/**
 * Aggregate score with its review count. With no reviews there is no score
 * to show, so it says so instead of rendering a misleading 0.0.
 */
withDefaults(
    defineProps<{
        score: number;
        reviewCount: number;
        size?: 'sm' | 'md' | 'lg';
    }>(),
    { size: 'md' },
);
</script>

<template>
    <span class="inline-flex flex-wrap items-center gap-2 text-sm">
        <template v-if="reviewCount > 0">
            <RatingStars :value="score" :size="size" />
            <span class="text-muted-foreground">
                {{ reviewCount }} {{ reviewCount === 1 ? 'review' : 'reviews' }}
            </span>
        </template>
        <span v-else class="text-muted-foreground">No reviews yet</span>
    </span>
</template>
