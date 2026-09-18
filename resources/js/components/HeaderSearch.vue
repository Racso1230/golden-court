<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { Search } from '@lucide/vue';
import { ref } from 'vue';
import { Input } from '@/components/ui/input';
import { index as venuesIndex } from '@/routes/venues';

const term = ref('');

function search(): void {
    const query = term.value.trim();

    router.get(venuesIndex.url(), query ? { term: query } : {});
}
</script>

<template>
    <form
        role="search"
        class="relative w-full max-w-xs"
        @submit.prevent="search"
    >
        <label for="header-search" class="sr-only">Search venues</label>
        <Search
            class="text-muted-foreground pointer-events-none absolute top-1/2 left-2.5 size-4 -translate-y-1/2"
            aria-hidden="true"
        />
        <Input
            id="header-search"
            v-model="term"
            type="search"
            name="term"
            placeholder="Search venues or cities"
            class="pl-8"
        />
    </form>
</template>
