<script setup lang="ts">
import type { Option } from '@/types';

/**
 * A styled native select. Native controls give screen readers and mobile
 * keyboards the best behaviour for short option lists.
 */
withDefaults(
    defineProps<{
        id: string;
        options: Option[];
        placeholder?: string | null;
    }>(),
    { placeholder: null },
);

const model = defineModel<string>({ default: '' });
</script>

<template>
    <select
        :id="id"
        v-model="model"
        class="border-input focus-visible:border-ring focus-visible:ring-ring/50 aria-invalid:border-destructive h-9 w-full rounded-md border bg-transparent px-3 text-sm shadow-xs transition-[color,box-shadow] outline-none focus-visible:ring-[3px]"
    >
        <option v-if="placeholder !== null" value="">{{ placeholder }}</option>
        <option
            v-for="option in options"
            :key="option.value"
            :value="option.value"
        >
            {{ option.label }}
        </option>
    </select>
</template>
