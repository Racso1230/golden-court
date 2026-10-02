<script setup lang="ts">
import { computed } from 'vue';

/**
 * Display-only star rating. The number is always shown alongside the stars
 * so colour and shape are never the only carriers of meaning.
 */
const props = withDefaults(
    defineProps<{
        value: number;
        size?: 'sm' | 'md' | 'lg';
        showValue?: boolean;
    }>(),
    { size: 'md', showValue: true },
);

const clamped = computed(() => Math.min(5, Math.max(0, props.value)));
const fillPercent = computed(() => (clamped.value / 5) * 100);
const label = computed(() => `${clamped.value.toFixed(1)} out of 5`);

const sizeClass = computed(
    () =>
        ({ sm: 'text-sm', md: 'text-base', lg: 'text-2xl' })[props.size] ??
        'text-base',
);
</script>

<template>
    <span
        class="inline-flex items-center gap-1.5"
        role="img"
        :aria-label="label"
    >
        <span
            class="relative inline-block leading-none tracking-tight select-none"
            :class="sizeClass"
            aria-hidden="true"
        >
            <span class="text-muted-foreground/30">★★★★★</span>
            <span
                class="text-gold-500 absolute inset-y-0 left-0 overflow-hidden whitespace-nowrap"
                :style="{ width: `${fillPercent}%` }"
                >★★★★★</span
            >
        </span>
        <span
            v-if="showValue"
            class="font-semibold tabular-nums"
            :class="size === 'sm' ? 'text-xs' : 'text-sm'"
            aria-hidden="true"
        >
            {{ clamped.toFixed(1) }}
        </span>
    </span>
</template>
