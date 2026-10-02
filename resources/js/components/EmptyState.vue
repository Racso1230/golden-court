<script setup lang="ts">
defineProps<{ title: string; description?: string }>();
</script>

<template>
    <div class="rounded-xl border border-dashed px-6 py-12 text-center">
        <span
            v-if="$slots.icon"
            class="bg-gold-50 text-gold-700 mx-auto mb-4 flex size-12 items-center justify-center rounded-full [&>svg]:size-5"
            aria-hidden="true"
        >
            <slot name="icon" />
        </span>
        <p class="font-medium">{{ title }}</p>
        <p
            v-if="description"
            class="text-muted-foreground mx-auto mt-1 max-w-md text-sm"
        >
            {{ description }}
        </p>
        <div v-if="$slots.default" class="mt-5 flex justify-center gap-2">
            <slot />
        </div>
    </div>
</template>
