<script setup lang="ts">
import type { DimensionAverages } from '@/types';

defineProps<{ averages: DimensionAverages }>();

const dimensions: { key: keyof DimensionAverages; label: string }[] = [
    { key: 'glass', label: 'Glass' },
    { key: 'lighting', label: 'Lighting' },
    { key: 'turf', label: 'Turf' },
    { key: 'facilities', label: 'Facilities' },
];
</script>

<template>
    <dl class="grid gap-3 sm:grid-cols-2">
        <div
            v-for="dimension in dimensions"
            :key="dimension.key"
            class="space-y-1"
        >
            <div class="flex items-baseline justify-between text-sm">
                <dt class="font-medium">{{ dimension.label }}</dt>
                <dd class="font-semibold tabular-nums">
                    {{
                        averages[dimension.key] === null
                            ? 'No data'
                            : `${averages[dimension.key]?.toFixed(1)} / 5`
                    }}
                </dd>
            </div>
            <div
                class="bg-muted h-2 overflow-hidden rounded-full"
                role="meter"
                :aria-label="`${dimension.label} average`"
                aria-valuemin="0"
                aria-valuemax="5"
                :aria-valuenow="averages[dimension.key] ?? 0"
                :aria-valuetext="
                    averages[dimension.key] === null
                        ? 'No data'
                        : `${averages[dimension.key]?.toFixed(1)} out of 5`
                "
            >
                <div
                    class="h-full rounded-full bg-amber-500 transition-[width]"
                    :style="{
                        width: `${((averages[dimension.key] ?? 0) / 5) * 100}%`,
                    }"
                />
            </div>
        </div>
    </dl>
</template>
