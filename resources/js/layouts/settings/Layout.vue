<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import PageHeader from '@/components/PageHeader.vue';
import { useCurrentUrl } from '@/composables/useCurrentUrl';
import { toUrl } from '@/lib/utils';
import { edit as editProfile } from '@/routes/profile';
import { edit as editSecurity } from '@/routes/security';
import type { NavItem } from '@/types';

/**
 * Wraps the settings pages inside AppLayout. "Settings" is the page's h1;
 * each settings page renders its own sections below.
 */
const items: NavItem[] = [
    { title: 'Profile', href: editProfile() },
    { title: 'Security', href: editSecurity() },
];

const { isCurrentOrParentUrl } = useCurrentUrl();
</script>

<template>
    <PageHeader
        eyebrow="Your account"
        title="Settings"
        description="Manage your profile and account security."
    />

    <div class="grid gap-8 lg:grid-cols-[12rem_1fr]">
        <aside>
            <nav aria-label="Settings" class="flex gap-1 lg:flex-col">
                <Link
                    v-for="item in items"
                    :key="toUrl(item.href)"
                    :href="item.href"
                    class="rounded-md px-3 py-2 text-sm transition-colors"
                    :class="
                        isCurrentOrParentUrl(item.href)
                            ? 'bg-accent text-foreground font-medium'
                            : 'text-muted-foreground hover:bg-accent hover:text-foreground'
                    "
                    :aria-current="
                        isCurrentOrParentUrl(item.href) ? 'page' : undefined
                    "
                >
                    {{ item.title }}
                </Link>
            </nav>
        </aside>

        <div class="max-w-2xl min-w-0 space-y-12">
            <slot />
        </div>
    </div>
</template>
