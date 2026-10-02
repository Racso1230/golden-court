import {
    Building2,
    LayoutGrid,
    MessageSquareText,
    Settings,
    ShieldCheck,
} from '@lucide/vue';
import { dashboard } from '@/routes';
import {
    claims as accountClaims,
    reviews as accountReviews,
} from '@/routes/account';
import { dashboard as adminDashboard } from '@/routes/admin';
import { index as adminClaims } from '@/routes/admin/claims';
import { index as adminFlags } from '@/routes/admin/flags';
import { pending as adminPendingReviews } from '@/routes/admin/reviews';
import { edit as editProfile } from '@/routes/profile';
import type { NavItem, SectionTab } from '@/types';

/**
 * The app's navigation in one place: the account and admin section tabs and
 * the user menu. Active tabs are matched on the Inertia page component, which
 * PHP tests pin, so this works the same on the server and in the browser.
 */

export type Section = 'account' | 'admin' | null;

export function sectionFor(component: string): Section {
    if (component.startsWith('Admin/')) return 'admin';
    if (
        component === 'Dashboard' ||
        component.startsWith('Account/') ||
        component.startsWith('settings/')
    ) {
        return 'account';
    }

    return null;
}

export function accountTabs(): SectionTab[] {
    return [
        {
            title: 'Overview',
            href: dashboard(),
            isActive: (component) => component === 'Dashboard',
        },
        {
            title: 'My reviews',
            href: accountReviews(),
            isActive: (component) => component === 'Account/Reviews',
        },
        {
            title: 'My claims',
            href: accountClaims(),
            isActive: (component) => component === 'Account/Claims',
        },
        {
            title: 'Settings',
            href: editProfile(),
            isActive: (component) => component.startsWith('settings/'),
        },
    ];
}

export function adminTabs(): SectionTab[] {
    return [
        {
            title: 'Overview',
            href: adminDashboard(),
            isActive: (component) => component === 'Admin/Dashboard',
        },
        {
            title: 'Claims',
            href: adminClaims(),
            isActive: (component) => component.startsWith('Admin/Claims/'),
        },
        {
            title: 'Flags',
            href: adminFlags(),
            isActive: (component) => component.startsWith('Admin/Flags/'),
        },
        {
            title: 'Pending reviews',
            href: adminPendingReviews(),
            isActive: (component) => component.startsWith('Admin/Reviews/'),
        },
    ];
}

export function userMenuItems(isAdmin: boolean): NavItem[] {
    return [
        { title: 'Dashboard', href: dashboard(), icon: LayoutGrid },
        {
            title: 'My reviews',
            href: accountReviews(),
            icon: MessageSquareText,
        },
        { title: 'My claims', href: accountClaims(), icon: Building2 },
        { title: 'Settings', href: editProfile(), icon: Settings },
        ...(isAdmin
            ? [
                  {
                      title: 'Moderation',
                      href: adminDashboard(),
                      icon: ShieldCheck,
                  },
              ]
            : []),
    ];
}
