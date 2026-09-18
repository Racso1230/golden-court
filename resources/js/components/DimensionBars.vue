<script setup lang="ts">
import type { DimensionAverages } from '@/types';

defineProps<{ averages: DimensionAverages }>();

const dimensions: { key: keyof DimensionAverages; label: string }[] = [
    { key: 'glass', label: 'Glass' },
    { key: 'lighting', label: 'Lighting' },
    { key: 'turf', label: 'Turf' },
    { key: 'facilities', label: 'Facilities' },
];

function describe(value: number | null): string {
    return value === null ? 'No data' : `${value.toFixed(1)} / 5`;
}
</script>

<template>
    <ul
        class="grid gap-3 sm:grid-cols-2"
        aria-label="Average score per dimension"
    >
        <li
            v-for="dimension in dimensions"
            :key="dimension.key"
            class="space-y-1"
        >
            <div class="flex items-baseline justify-between text-sm">
                <span :id="`dimension-${dimension.key}`" class="font-medium">
                    {{ dimension.label }}
                </span>
                <span class="font-semibold tabular-nums">
                    {{ describe(averages[dimension.key]) }}
                </span>
            </div>
            <div
                class="bg-muted h-2 overflow-hidden rounded-full"
                role="meter"
                :aria-labelledby="`dimension-${dimension.key}`"
                aria-valuemin="0"
                aria-valuemax="5"
                :aria-valuenow="averages[dimension.key] ?? 0"
                :aria-valuetext="describe(averages[dimension.key])"
            >
                <div
                    class="h-full rounded-full bg-amber-500 transition-[width]"
                    :style="{
                        width: `${((averages[dimension.key] ?? 0) / 5) * 100}%`,
                    }"
                />
            </div>
        </li>
    </ul>
</template>
