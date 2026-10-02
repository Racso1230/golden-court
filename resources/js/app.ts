import { createInertiaApp } from '@inertiajs/vue3';
import { resolveLayout } from '@/inertia';
import { initializeFlashToast } from '@/lib/flashToast';

void createInertiaApp({
    layout: resolveLayout,
    // Head tags are built on the server and arrive as the `head` prop.
    serverHead: true,
    progress: {
        color: '#4B5563',
    },
});

// This will listen for flash toast data from the server...
initializeFlashToast();
