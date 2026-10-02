import type { Component } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import AuthLayout from '@/layouts/AuthLayout.vue';
import PublicLayout from '@/layouts/PublicLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';

/**
 * Picks the persistent layout for a page by its name. Shared by the browser
 * entry point and the SSR render guard so both render pages the same way.
 */
export function resolveLayout(name: string): Component | Component[] {
    switch (true) {
        case name === 'Home':
        case name.startsWith('Venues/'):
        case name.startsWith('Courts/'):
            return PublicLayout;
        case name.startsWith('auth/'):
            return AuthLayout;
        case name.startsWith('settings/'):
            return [AppLayout, SettingsLayout];
        default:
            return AppLayout;
    }
}
