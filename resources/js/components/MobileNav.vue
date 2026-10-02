<script setup lang="ts">
import { Link, router, usePage } from '@inertiajs/vue3';
import { LogOut, Menu } from '@lucide/vue';
import { computed, ref } from 'vue';
import AppLogo from '@/components/AppLogo.vue';
import HeaderSearch from '@/components/HeaderSearch.vue';
import { Button } from '@/components/ui/button';
import {
    Sheet,
    SheetContent,
    SheetDescription,
    SheetTitle,
    SheetTrigger,
} from '@/components/ui/sheet';
import { userMenuItems } from '@/lib/navigation';
import { toUrl } from '@/lib/utils';
import { login, logout, register } from '@/routes';
import { index as venuesIndex } from '@/routes/venues';

const page = usePage();
// Guests have no user even though the shared type says otherwise.
const user = computed(() => page.props.auth.user ?? null);
const items = computed(() =>
    user.value === null ? [] : userMenuItems(user.value.role === 'admin'),
);

const open = ref(false);

function close(): void {
    open.value = false;
}

function logOut(): void {
    close();
    router.flushAll();
}
</script>

<template>
    <Sheet v-model:open="open">
        <SheetTrigger as-child>
            <Button
                variant="ghost"
                size="icon"
                class="lg:hidden"
                aria-label="Open menu"
            >
                <Menu aria-hidden="true" />
            </Button>
        </SheetTrigger>
        <SheetContent side="left" class="w-80 gap-0 p-0">
            <SheetTitle class="sr-only">Menu</SheetTitle>
            <SheetDescription class="sr-only">
                Site navigation and your account
            </SheetDescription>
            <div class="border-b px-5 py-4">
                <AppLogo />
            </div>
            <div class="border-b px-5 py-4">
                <HeaderSearch
                    input-id="mobile-search"
                    class="max-w-none"
                    @searched="close"
                />
            </div>
            <nav aria-label="Mobile" class="flex-1 overflow-y-auto px-3 py-3">
                <ul class="space-y-1 text-sm">
                    <li>
                        <Link
                            :href="venuesIndex()"
                            class="hover:bg-accent block rounded-md px-3 py-2 font-medium"
                            @click="close"
                            >Venues</Link
                        >
                    </li>
                    <li v-for="item in items" :key="toUrl(item.href)">
                        <Link
                            :href="item.href"
                            class="hover:bg-accent flex items-center gap-3 rounded-md px-3 py-2"
                            @click="close"
                        >
                            <component
                                :is="item.icon"
                                class="text-muted-foreground size-4"
                                aria-hidden="true"
                            />
                            {{ item.title }}
                        </Link>
                    </li>
                </ul>
            </nav>
            <div class="border-t px-5 py-4">
                <Link
                    v-if="user"
                    :href="logout()"
                    as="button"
                    class="text-muted-foreground hover:text-foreground flex items-center gap-2 text-sm"
                    @click="logOut"
                >
                    <LogOut class="size-4" aria-hidden="true" />
                    Log out
                </Link>
                <div v-else class="grid grid-cols-2 gap-2">
                    <Button variant="outline" as-child>
                        <Link :href="login()" @click="close">Log in</Link>
                    </Button>
                    <Button as-child>
                        <Link :href="register()" @click="close">Register</Link>
                    </Button>
                </div>
            </div>
        </SheetContent>
    </Sheet>
</template>
