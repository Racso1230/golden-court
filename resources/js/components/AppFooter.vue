<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import AppLogo from '@/components/AppLogo.vue';
import { dashboard, home, login, register } from '@/routes';
import { index as venuesIndex } from '@/routes/venues';

const page = usePage();
// Guests have no user even though the shared type says otherwise.
const signedIn = computed(() => (page.props.auth.user ?? null) !== null);
// Fixed at render time so the server and the browser agree.
const year = new Date().getUTCFullYear();
</script>

<template>
    <footer class="mt-16 border-t">
        <div
            class="mx-auto grid max-w-6xl gap-8 px-4 py-10 sm:grid-cols-[1fr_auto] sm:px-6 lg:px-8"
        >
            <div class="space-y-3">
                <Link :href="home()" class="inline-flex">
                    <AppLogo size="sm" />
                </Link>
                <p class="text-muted-foreground max-w-md text-sm">
                    Independent player reviews of padel courts: glass, lighting,
                    turf and facilities, scored by people who have played there.
                </p>
            </div>
            <nav aria-label="Footer" class="text-sm">
                <ul class="flex flex-wrap gap-x-6 gap-y-2 sm:flex-col">
                    <li>
                        <Link
                            :href="venuesIndex()"
                            class="text-muted-foreground hover:text-foreground"
                            >Venues</Link
                        >
                    </li>
                    <template v-if="signedIn">
                        <li>
                            <Link
                                :href="dashboard()"
                                class="text-muted-foreground hover:text-foreground"
                                >Dashboard</Link
                            >
                        </li>
                    </template>
                    <template v-else>
                        <li>
                            <Link
                                :href="login()"
                                class="text-muted-foreground hover:text-foreground"
                                >Log in</Link
                            >
                        </li>
                        <li>
                            <Link
                                :href="register()"
                                class="text-muted-foreground hover:text-foreground"
                                >Create an account</Link
                            >
                        </li>
                    </template>
                </ul>
            </nav>
        </div>
        <div class="border-t">
            <p
                class="text-muted-foreground mx-auto max-w-6xl px-4 py-4 text-xs sm:px-6 lg:px-8"
            >
                © {{ year }} {{ page.props.name }}. No booking, no ads, just
                reviews.
            </p>
        </div>
    </footer>
</template>
