<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { toUrl } from '@/lib/utils';
import type { SectionTab } from '@/types';

defineProps<{ label: string; items: SectionTab[] }>();

const page = usePage();
</script>

<template>
    <div class="border-b">
        <nav
            :aria-label="label"
            class="mx-auto flex max-w-6xl gap-1 overflow-x-auto px-4 sm:px-6 lg:px-8"
        >
            <Link
                v-for="item in items"
                :key="toUrl(item.href)"
                :href="item.href"
                class="relative px-3 py-3 text-sm font-medium whitespace-nowrap transition-colors"
                :class="
                    item.isActive(page.component)
                        ? 'text-foreground after:bg-gold-500 after:absolute after:inset-x-3 after:bottom-0 after:h-0.5 after:rounded-full'
                        : 'text-muted-foreground hover:text-foreground'
                "
                :aria-current="
                    item.isActive(page.component) ? 'page' : undefined
                "
            >
                {{ item.title }}
            </Link>
        </nav>
    </div>
</template>
