<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import AppLogo from '@/components/AppLogo.vue';
import { Toaster } from '@/components/ui/sonner';
import { home } from '@/routes';

/**
 * Log in, registration and the other account-access pages: the brand, one
 * card holding the page's h1 and form, and the toaster so flash messages
 * show here too. `title` and `description` are set by each page's layout
 * options; no auth page has props of those names.
 */
const { title = '', description = '' } = defineProps<{
    title?: string;
    description?: string;
}>();
</script>

<template>
    <div class="bg-muted/40 flex min-h-svh flex-col">
        <header
            class="mx-auto flex w-full max-w-6xl items-center px-4 py-5 sm:px-6 lg:px-8"
        >
            <Link :href="home()">
                <AppLogo />
            </Link>
        </header>
        <main
            class="flex flex-1 items-start justify-center px-4 pt-4 pb-16 sm:pt-10"
        >
            <div
                class="bg-card w-full max-w-sm rounded-2xl border p-8 shadow-xs"
            >
                <h1 class="font-display text-3xl tracking-tight">
                    {{ title }}
                </h1>
                <p
                    v-if="description"
                    class="text-muted-foreground mt-1.5 text-sm"
                >
                    {{ description }}
                </p>
                <div class="mt-8">
                    <slot />
                </div>
            </div>
        </main>
        <Toaster />
    </div>
</template>
