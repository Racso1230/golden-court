import type { Auth } from '@/types/auth';

// Extend ImportMeta interface for Vite...
declare module 'vite/client' {
    interface ImportMetaEnv {
        readonly VITE_APP_NAME: string;
        [key: string]: string | boolean | undefined;
    }

    interface ImportMeta {
        readonly env: ImportMetaEnv;
        readonly glob: <T>(pattern: string) => Record<string, () => Promise<T>>;
    }
}

declare module '@inertiajs/core' {
    export interface InertiaConfig {
        sharedPageProps: {
            name: string;
            /** False when sign-up is switched off (GOLDEN_COURT_REGISTRATION=false). */
            canRegister: boolean;
            /** True on the public concept deployment (GOLDEN_COURT_CONCEPT_BANNER=true). */
            conceptBanner: boolean;
            auth: Auth;
            /** Null for guests. */
            notifications: App.Domain.Users.Data.NotificationsSummaryData | null;
            /** Head elements as HTML strings, built on the server (app/Support/Seo). */
            head: string[];
            [key: string]: unknown;
        };
    }
}

declare module 'vue' {
    interface ComponentCustomProperties {
        $inertia: typeof Router;
        $page: Page;
        $headManager: ReturnType<typeof createHeadManager>;
    }
}
