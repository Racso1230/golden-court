import type { Component } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import AuthLayout from '@/layouts/AuthLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';

/**
 * Picks the persistent layout for a page by its name. Shared by the browser
 * entry point and the SSR render guard so both render pages the same way.
 */
export function resolveLayout(name: string): Component | Component[] {
    if (name.startsWith('auth/')) return AuthLayout;
    if (name.startsWith('settings/')) return [AppLayout, SettingsLayout];

    return AppLayout;
}
