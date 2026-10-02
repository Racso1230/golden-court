<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ChevronRight } from '@lucide/vue';
import type { BreadcrumbItem } from '@/types';

/**
 * The top of a page and its only h1. Breadcrumbs, when given, lead to the
 * current page, which is the title itself.
 */
withDefaults(
    defineProps<{
        title: string;
        description?: string;
        eyebrow?: string;
        breadcrumbs?: BreadcrumbItem[];
    }>(),
    { description: undefined, eyebrow: undefined, breadcrumbs: () => [] },
);
</script>

<template>
    <header
        class="mb-8 flex flex-wrap items-end justify-between gap-x-6 gap-y-4"
    >
        <div class="min-w-0">
            <nav
                v-if="breadcrumbs.length > 0"
                aria-label="Breadcrumb"
                class="mb-3"
            >
                <ol
                    class="text-muted-foreground flex flex-wrap items-center gap-1.5 text-sm"
                >
                    <li
                        v-for="crumb in breadcrumbs"
                        :key="crumb.title"
                        class="flex items-center gap-1.5"
                    >
                        <Link
                            :href="crumb.href"
                            class="hover:text-foreground transition-colors"
                        >
                            {{ crumb.title }}
                        </Link>
                        <ChevronRight class="size-3.5" aria-hidden="true" />
                    </li>
                    <li aria-current="page" class="text-foreground truncate">
                        {{ title }}
                    </li>
                </ol>
            </nav>
            <p
                v-else-if="eyebrow"
                class="text-gold-700 mb-2 text-xs font-semibold tracking-[0.14em] uppercase"
            >
                {{ eyebrow }}
            </p>
            <h1 class="font-display text-3xl tracking-tight sm:text-4xl">
                {{ title }}
            </h1>
            <p v-if="description" class="text-muted-foreground mt-2 max-w-2xl">
                {{ description }}
            </p>
            <div v-if="$slots.meta" class="mt-3">
                <slot name="meta" />
            </div>
        </div>
        <div v-if="$slots.actions" class="flex flex-wrap items-center gap-2">
            <slot name="actions" />
        </div>
    </header>
</template>
