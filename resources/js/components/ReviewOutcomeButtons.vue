<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import ConfirmDialog from '@/components/ConfirmDialog.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import type { ReviewStatus } from '@/types';

/**
 * Publish / remove decision for a review. Publishing is immediate; removing
 * a review is destructive and asks for confirmation first.
 */
const props = withDefaults(
    defineProps<{
        action: string;
        publishLabel?: string;
        removeLabel?: string;
    }>(),
    { publishLabel: 'Publish', removeLabel: 'Remove' },
);

const form = useForm<{ outcome: ReviewStatus }>({ outcome: 'published' });

function decide(outcome: ReviewStatus): void {
    form.transform(() => ({ outcome })).post(props.action, {
        preserveScroll: true,
    });
}
</script>

<template>
    <div class="flex flex-wrap items-start gap-2">
        <Button
            type="button"
            size="sm"
            :disabled="form.processing"
            @click="decide('published')"
        >
            {{ publishLabel }}
        </Button>
        <ConfirmDialog
            title="Remove this review?"
            description="The review disappears from the court page and its author is notified. This cannot be undone."
            confirm-label="Remove review"
            :processing="form.processing"
            @confirm="decide('removed')"
        >
            <template #trigger>
                <Button type="button" size="sm" variant="destructive">
                    {{ removeLabel }}
                </Button>
            </template>
        </ConfirmDialog>
        <InputError :message="form.errors.outcome" />
    </div>
</template>
