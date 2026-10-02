<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import AppLogoIcon from '@/components/AppLogoIcon.vue';

/** The mark with the wordmark beside it. */
const props = withDefaults(defineProps<{ size?: 'sm' | 'md' | 'lg' }>(), {
    size: 'md',
});

const name = usePage().props.name;

const classes = computed(
    () =>
        ({
            sm: { icon: 'size-6', text: 'text-lg' },
            md: { icon: 'size-7', text: 'text-xl' },
            lg: { icon: 'size-9', text: 'text-2xl' },
        })[props.size],
);
</script>

<template>
    <span class="flex items-center gap-2">
        <AppLogoIcon :class="['shrink-0', classes.icon]" />
        <span
            class="font-display leading-none tracking-tight"
            :class="classes.text"
            >{{ name }}</span
        >
    </span>
</template>
