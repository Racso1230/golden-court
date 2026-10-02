<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { Search } from '@lucide/vue';
import { ref } from 'vue';
import { Input } from '@/components/ui/input';
import { index as venuesIndex } from '@/routes/venues';

// The header and the mobile menu each render one, so ids must differ.
const { inputId = 'header-search' } = defineProps<{ inputId?: string }>();

const emit = defineEmits<{ searched: [] }>();

const term = ref('');

function search(): void {
    const query = term.value.trim();

    router.get(venuesIndex.url(), query ? { term: query } : {});
    emit('searched');
}
</script>

<template>
    <form
        role="search"
        class="relative w-full max-w-xs"
        @submit.prevent="search"
    >
        <label :for="inputId" class="sr-only">Search venues</label>
        <Search
            class="text-muted-foreground pointer-events-none absolute top-1/2 left-2.5 size-4 -translate-y-1/2"
            aria-hidden="true"
        />
        <Input
            :id="inputId"
            v-model="term"
            type="search"
            name="term"
            placeholder="Search venues or cities"
            class="pl-8"
        />
    </form>
</template>
