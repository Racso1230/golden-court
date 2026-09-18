<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import DestroyReviewReplyController from '@/actions/App/Http/Controllers/Reviews/DestroyReviewReplyController';
import StoreReviewReplyController from '@/actions/App/Http/Controllers/Reviews/StoreReviewReplyController';
import UpdateReviewReplyController from '@/actions/App/Http/Controllers/Reviews/UpdateReviewReplyController';
import InputError from '@/components/InputError.vue';
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
import { Label } from '@/components/ui/label';
import type { ReplyFormData, ReviewReply } from '@/types';

/**
 * Inline owner reply. Creates when there is no reply yet, otherwise edits
 * the existing one and offers a confirmed delete.
 */
const props = defineProps<{
    reviewId: number;
    reply: ReviewReply | null;
}>();

const editing = ref(props.reply === null);

const form = useForm<ReplyFormData>({ body: props.reply?.body ?? '' });

function submit(): void {
    const options = {
        preserveScroll: true,
        onSuccess: () => {
            editing.value = false;
        },
    };

    if (props.reply === null) {
        form.post(StoreReviewReplyController.url(props.reviewId), options);
    } else {
        form.patch(UpdateReviewReplyController.url(props.reviewId), options);
    }
}

function destroy(): void {
    form.delete(DestroyReviewReplyController.url(props.reviewId), {
        preserveScroll: true,
    });
}
</script>

<template>
    <div class="space-y-2">
        <form v-if="editing" class="space-y-2" @submit.prevent="submit">
            <Label :for="`reply-${reviewId}`">
                {{ reply ? 'Edit your reply' : 'Reply as the venue' }}
            </Label>
            <textarea
                :id="`reply-${reviewId}`"
                v-model="form.body"
                required
                maxlength="1000"
                rows="3"
                class="border-input bg-background focus-visible:ring-ring/50 w-full rounded-md border px-3 py-2 text-sm shadow-xs outline-none focus-visible:ring-[3px]"
                placeholder="Thank the player or explain what has changed since."
            />
            <InputError :message="form.errors.body" />
            <div class="flex gap-2">
                <Button type="submit" size="sm" :disabled="form.processing">
                    {{ reply ? 'Save reply' : 'Post reply' }}
                </Button>
                <Button
                    v-if="reply"
                    type="button"
                    size="sm"
                    variant="ghost"
                    @click="editing = false"
                >
                    Cancel
                </Button>
            </div>
        </form>

        <div v-else class="flex gap-2">
            <Button
                type="button"
                size="sm"
                variant="outline"
                @click="editing = true"
            >
                Edit reply
            </Button>
            <Dialog>
                <DialogTrigger as-child>
                    <Button type="button" size="sm" variant="destructive">
                        Delete reply
                    </Button>
                </DialogTrigger>
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>Delete this reply?</DialogTitle>
                        <DialogDescription>
                            The player will no longer see your response. You can
                            post a new one afterwards.
                        </DialogDescription>
                    </DialogHeader>
                    <DialogFooter>
                        <Button
                            type="button"
                            variant="destructive"
                            :disabled="form.processing"
                            @click="destroy"
                        >
                            Delete reply
                        </Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>
        </div>
    </div>
</template>
