<script setup lang="ts">
defineProps<{
    score: number;
    reviewCount: number;
}>();
</script>

<template>
    <span class="inline-flex items-baseline gap-1.5 text-sm">
        <span
            v-if="reviewCount > 0"
            class="rounded-md bg-amber-100 px-2 py-0.5 font-semibold text-amber-900 tabular-nums dark:bg-amber-900/40 dark:text-amber-200"
        >
            {{ score.toFixed(1) }}
        </span>
        <span v-else class="text-muted-foreground">No reviews yet</span>
        <span v-if="reviewCount > 0" class="text-muted-foreground">
            {{ reviewCount }} {{ reviewCount === 1 ? 'review' : 'reviews' }}
        </span>
    </span>
</template>
