<script setup lang="ts">
withDefaults(
    defineProps<{
        title?: string;
        description?: string;
        as?: 'section' | 'div';
    }>(),
    { title: undefined, description: undefined, as: 'section' },
);
</script>

<template>
    <component :is="as" class="bg-card rounded-xl border shadow-xs">
        <header
            v-if="title || $slots.actions"
            class="flex flex-wrap items-start justify-between gap-4 border-b px-5 py-4"
        >
            <div>
                <h2 v-if="title" class="text-base font-semibold">
                    {{ title }}
                </h2>
                <p
                    v-if="description"
                    class="text-muted-foreground mt-0.5 text-sm"
                >
                    {{ description }}
                </p>
            </div>
            <div v-if="$slots.actions" class="flex items-center gap-2">
                <slot name="actions" />
            </div>
        </header>
        <div class="p-5">
            <slot />
        </div>
    </component>
</template>
