<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import AppLogo from '@/components/AppLogo.vue';
import HeaderSearch from '@/components/HeaderSearch.vue';
import MobileNav from '@/components/MobileNav.vue';
import NotificationsMenu from '@/components/NotificationsMenu.vue';
import { Button } from '@/components/ui/button';
import UserMenu from '@/components/UserMenu.vue';
import { dashboard, home, login, register } from '@/routes';
import { dashboard as adminDashboard } from '@/routes/admin';
import { index as venuesIndex } from '@/routes/venues';

const page = usePage();
// Guests have no user even though the shared type says otherwise.
const user = computed(() => page.props.auth.user ?? null);

const links = computed(() => [
    {
        title: 'Venues',
        href: venuesIndex(),
        active:
            page.component.startsWith('Venues/') ||
            page.component.startsWith('Courts/'),
    },
    ...(user.value
        ? [
              {
                  title: 'Dashboard',
                  href: dashboard(),
                  active:
                      page.component === 'Dashboard' ||
                      page.component.startsWith('Account/') ||
                      page.component.startsWith('settings/'),
              },
          ]
        : []),
    ...(user.value?.role === 'admin'
        ? [
              {
                  title: 'Admin',
                  href: adminDashboard(),
                  active: page.component.startsWith('Admin/'),
              },
          ]
        : []),
]);
</script>

<template>
    <header
        class="bg-background/95 supports-[backdrop-filter]:bg-background/80 sticky top-0 z-40 border-b backdrop-blur"
    >
        <div
            class="mx-auto flex h-16 max-w-6xl items-center gap-2 px-4 sm:gap-4 sm:px-6 lg:px-8"
        >
            <MobileNav />
            <Link :href="home()" class="mr-2 shrink-0">
                <AppLogo />
            </Link>

            <nav aria-label="Main" class="hidden h-full items-center lg:flex">
                <Link
                    v-for="link in links"
                    :key="link.title"
                    :href="link.href"
                    class="relative flex h-full items-center px-3 text-sm font-medium transition-colors"
                    :class="
                        link.active
                            ? 'text-foreground after:bg-gold-500 after:absolute after:inset-x-3 after:bottom-0 after:h-0.5 after:rounded-full'
                            : 'text-muted-foreground hover:text-foreground'
                    "
                    :aria-current="link.active ? 'page' : undefined"
                >
                    {{ link.title }}
                </Link>
            </nav>

            <div class="ml-auto flex items-center gap-2">
                <HeaderSearch class="hidden md:block md:w-64" />
                <template v-if="user">
                    <NotificationsMenu />
                    <UserMenu :user="user" />
                </template>
                <template v-else>
                    <Button
                        variant="ghost"
                        as-child
                        class="hidden sm:inline-flex"
                    >
                        <Link :href="login()">Log in</Link>
                    </Button>
                    <Button as-child>
                        <Link :href="register()">Register</Link>
                    </Button>
                </template>
            </div>
        </div>
    </header>
</template>
