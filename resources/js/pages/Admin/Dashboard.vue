<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import Heading from '@/components/Heading.vue';
import { index as claimsIndex } from '@/routes/admin/claims';
import { index as flagsIndex } from '@/routes/admin/flags';
import { pending as pendingReviews } from '@/routes/admin/reviews';

// Minimal shape for this phase; generated Data types arrive in Phase 7.
type Counts = {
    pendingClaims: number;
    flaggedReviews: number;
    pendingReviews: number;
};

defineProps<{ counts: Counts }>();
</script>

<template>
    <Head title="Admin" />

    <div class="flex flex-col space-y-6 p-4">
        <Heading
            title="Moderation"
            description="What needs a decision right now"
        />

        <div class="grid gap-4 sm:grid-cols-3">
            <Link
                :href="claimsIndex()"
                class="hover:bg-accent rounded-xl border p-4"
            >
                <p class="text-muted-foreground text-sm">Pending claims</p>
                <p class="text-3xl font-semibold tabular-nums">
                    {{ counts.pendingClaims }}
                </p>
            </Link>
            <Link
                :href="flagsIndex()"
                class="hover:bg-accent rounded-xl border p-4"
            >
                <p class="text-muted-foreground text-sm">
                    Reviews with open flags
                </p>
                <p class="text-3xl font-semibold tabular-nums">
                    {{ counts.flaggedReviews }}
                </p>
            </Link>
            <Link
                :href="pendingReviews()"
                class="hover:bg-accent rounded-xl border p-4"
            >
                <p class="text-muted-foreground text-sm">Pending reviews</p>
                <p class="text-3xl font-semibold tabular-nums">
                    {{ counts.pendingReviews }}
                </p>
            </Link>
        </div>
    </div>
</template>
