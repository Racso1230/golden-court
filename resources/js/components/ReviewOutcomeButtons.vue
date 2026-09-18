<script setup lang="ts">
import { Form } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';

/**
 * Publish / remove buttons that post an `outcome` to the given form binding.
 */
defineProps<{
    form: { action: string; method: 'post' | 'get' };
    publishLabel?: string;
    removeLabel?: string;
}>();
</script>

<template>
    <div class="flex flex-wrap items-start gap-2">
        <Form v-bind="form" v-slot="{ processing, errors }">
            <input type="hidden" name="outcome" value="published" />
            <Button type="submit" :disabled="processing">
                {{ publishLabel ?? 'Publish' }}
            </Button>
            <InputError :message="errors.outcome" />
        </Form>
        <Form v-bind="form" v-slot="{ processing }">
            <input type="hidden" name="outcome" value="removed" />
            <Button type="submit" variant="destructive" :disabled="processing">
                {{ removeLabel ?? 'Remove' }}
            </Button>
        </Form>
    </div>
</template>
