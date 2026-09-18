<script setup lang="ts">
import { ref } from 'vue';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';

/**
 * A confirm step for destructive actions. The trigger slot renders the
 * button that opens it; `confirm` fires when the user commits.
 */
withDefaults(
    defineProps<{
        title: string;
        description: string;
        confirmLabel?: string;
        processing?: boolean;
    }>(),
    { confirmLabel: 'Confirm', processing: false },
);

const emit = defineEmits<{ confirm: [] }>();

const open = ref(false);

function confirm(): void {
    emit('confirm');
    open.value = false;
}
</script>

<template>
    <Dialog v-model:open="open">
        <DialogTrigger as-child>
            <slot name="trigger" />
        </DialogTrigger>
        <DialogContent>
            <DialogHeader>
                <DialogTitle>{{ title }}</DialogTitle>
                <DialogDescription>{{ description }}</DialogDescription>
            </DialogHeader>
            <slot />
            <DialogFooter>
                <Button type="button" variant="ghost" @click="open = false">
                    Cancel
                </Button>
                <Button
                    type="button"
                    variant="destructive"
                    :disabled="processing"
                    @click="confirm"
                >
                    {{ confirmLabel }}
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
