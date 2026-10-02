<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import type { PaginationLink } from '@/types';

defineProps<{ links: PaginationLink[] }>();
</script>

<template>
    <nav
        v-if="links.length > 3"
        class="flex flex-wrap items-center gap-1 text-sm"
        aria-label="Pagination"
    >
        <template v-for="(link, index) in links" :key="index">
            <Link
                v-if="link.url"
                :href="link.url"
                class="hover:bg-accent rounded-md border px-3 py-1.5"
                :class="{
                    'border-gold-500 bg-gold-50 text-gold-900 font-semibold':
                        link.active,
                }"
                :aria-current="link.active ? 'page' : undefined"
                v-html="link.label"
            />
            <span
                v-else
                class="text-muted-foreground rounded-md border px-3 py-1.5"
                v-html="link.label"
            />
        </template>
    </nav>
</template>
