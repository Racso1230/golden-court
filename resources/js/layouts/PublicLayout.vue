<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import HeaderSearch from '@/components/HeaderSearch.vue';
import { Toaster } from '@/components/ui/sonner';
import { dashboard, home, login, register } from '@/routes';
import { index as venuesIndex } from '@/routes/venues';

const page = usePage();
// Guests have no user even though the shared type says otherwise.
const user = computed(() => page.props.auth.user ?? null);
</script>

<template>
    <div class="bg-background text-foreground flex min-h-screen flex-col">
        <header class="border-b">
            <nav
                class="mx-auto flex w-full max-w-5xl flex-wrap items-center justify-between gap-4 px-4 py-3"
                aria-label="Main"
            >
                <Link
                    :href="home()"
                    class="flex items-center gap-2 font-semibold"
                >
                    <AppLogoIcon class="size-6 fill-current" />
                    <span>{{ page.props.name }}</span>
                </Link>

                <HeaderSearch
                    class="order-last w-full sm:order-none sm:w-auto"
                />

                <div class="flex items-center gap-4 text-sm">
                    <Link :href="venuesIndex()" class="hover:underline">
                        Venues
                    </Link>
                    <template v-if="user">
                        <Link :href="dashboard()" class="hover:underline">
                            Dashboard
                        </Link>
                    </template>
                    <template v-else>
                        <Link :href="login()" class="hover:underline">
                            Log in
                        </Link>
                        <Link
                            :href="register()"
                            class="hover:bg-accent rounded-md border px-3 py-1.5"
                        >
                            Register
                        </Link>
                    </template>
                </div>
            </nav>
        </header>

        <main class="mx-auto w-full max-w-5xl flex-1 px-4 py-8">
            <slot />
        </main>

        <Toaster />
    </div>
</template>
