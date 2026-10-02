<script setup lang="ts">
import type { InertiaLinkProps } from '@inertiajs/vue3';
import { Link } from '@inertiajs/vue3';

withDefaults(
    defineProps<{
        label: string;
        value: number | string;
        hint?: string;
        href?: NonNullable<InertiaLinkProps['href']>;
    }>(),
    { hint: undefined, href: undefined },
);
</script>

<template>
    <component
        :is="href ? Link : 'div'"
        :href="href"
        class="bg-card flex h-full flex-col rounded-xl border p-5 shadow-xs transition"
        :class="{ 'hover:border-gold-300 hover:shadow-sm': href }"
    >
        <span
            class="text-muted-foreground [&>svg]:text-gold-600 flex items-center gap-2 text-sm [&>svg]:size-4"
        >
            <slot name="icon" />
            {{ label }}
        </span>
        <span class="mt-2 text-3xl font-semibold tabular-nums">{{
            value
        }}</span>
        <span v-if="hint" class="text-muted-foreground mt-1 text-xs">{{
            hint
        }}</span>
    </component>
</template>
