<script setup lang="ts">
/**
 * A section heading inside a page. The page title is PageHeader's h1, so this
 * renders an h2 by default, or an h3 for nested sections.
 */
type Props = {
    title: string;
    description?: string;
    variant?: 'default' | 'small';
    level?: 2 | 3;
};

withDefaults(defineProps<Props>(), {
    description: undefined,
    variant: 'default',
    level: 2,
});
</script>

<template>
    <header :class="variant === 'small' ? '' : 'mb-8 space-y-0.5'">
        <component
            :is="`h${level}`"
            :class="
                variant === 'small'
                    ? 'mb-0.5 text-base font-medium'
                    : 'text-xl font-semibold tracking-tight'
            "
        >
            {{ title }}
        </component>
        <p v-if="description" class="text-muted-foreground text-sm">
            {{ description }}
        </p>
    </header>
</template>
