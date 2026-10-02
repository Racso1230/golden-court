<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import AppFooter from '@/components/AppFooter.vue';
import AppHeader from '@/components/AppHeader.vue';
import SectionTabs from '@/components/SectionTabs.vue';
import { Toaster } from '@/components/ui/sonner';
import { accountTabs, adminTabs, sectionFor } from '@/lib/navigation';

/**
 * The one layout for every page outside auth: header, the section tabs for
 * the account and admin areas, the page, the footer and the toaster. It takes
 * no props, because Inertia passes every page prop to its layouts and a
 * layout prop must never collide with one.
 */
const page = usePage();
const section = computed(() => sectionFor(page.component));
</script>

<template>
    <div class="flex min-h-svh flex-col">
        <a
            href="#main"
            class="focus:bg-background sr-only focus:not-sr-only focus:absolute focus:top-2 focus:left-2 focus:z-50 focus:rounded-md focus:px-3 focus:py-2"
            >Skip to content</a
        >
        <AppHeader />
        <SectionTabs
            v-if="section === 'account'"
            label="Account"
            :items="accountTabs()"
        />
        <SectionTabs
            v-else-if="section === 'admin'"
            label="Admin"
            :items="adminTabs()"
        />
        <main
            id="main"
            class="mx-auto w-full max-w-6xl flex-1 px-4 py-8 sm:px-6 lg:px-8 lg:py-10"
        >
            <slot />
        </main>
        <AppFooter />
        <Toaster />
    </div>
</template>
