<script setup lang="ts">
import type { HTMLAttributes } from 'vue';
import { useId } from 'vue';

/**
 * The Golden Court mark: a gold padel ball with a white star, for the sport
 * and its ratings. Same shape as resources/brand/mark.svg. It carries its own
 * colours, so callers only size it.
 */
defineOptions({
    inheritAttrs: false,
});

type Props = {
    className?: HTMLAttributes['class'];
};

defineProps<Props>();

// Unique per instance and stable between server and client renders.
const gradientId = `gc-ball-${useId()}`;
</script>

<template>
    <svg
        xmlns="http://www.w3.org/2000/svg"
        viewBox="0 0 64 64"
        aria-hidden="true"
        :class="className"
        v-bind="$attrs"
    >
        <defs>
            <radialGradient :id="gradientId" cx="36%" cy="30%" r="75%">
                <stop offset="0" stop-color="#ffd162" />
                <stop offset="0.5" stop-color="#eaa000" />
                <stop offset="1" stop-color="#a86a00" />
            </radialGradient>
        </defs>
        <circle cx="32" cy="32" r="30" :fill="`url(#${gradientId})`" />
        <path
            d="M12.5 10.5c6.5 10 6.5 33 0 43M51.5 10.5c-6.5 10-6.5 33 0 43"
            fill="none"
            stroke="#ffffff"
            stroke-width="4"
            stroke-linecap="round"
        />
        <path
            d="M32 19.5l3.7 7.6 8.3 1.2-6 5.9 1.4 8.3-7.4-3.9-7.4 3.9 1.4-8.3-6-5.9 8.3-1.2z"
            fill="#ffffff"
        />
    </svg>
</template>
