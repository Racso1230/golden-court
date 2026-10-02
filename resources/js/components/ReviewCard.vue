<script setup lang="ts">
import { Link, router, useForm } from '@inertiajs/vue3';
import { BadgeCheck, Flag, ThumbsUp } from '@lucide/vue';
import { ref, watch } from 'vue';
import FlagReviewController from '@/actions/App/Http/Controllers/Moderation/FlagReviewController';
import ToggleReviewVoteController from '@/actions/App/Http/Controllers/Reviews/ToggleReviewVoteController';
import InputError from '@/components/InputError.vue';
import NativeSelect from '@/components/NativeSelect.vue';
import RatingStars from '@/components/RatingStars.vue';
import ReplyForm from '@/components/ReplyForm.vue';
import { Badge } from '@/components/ui/badge';
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
import { formatDate } from '@/lib/dates';
import { edit as reviewEdit } from '@/routes/reviews';
import type { FlagReviewFormData, Option, Review } from '@/types';

const props = withDefaults(
    defineProps<{
        review: Review;
        signedIn: boolean;
        canReply?: boolean;
        canEdit?: boolean;
        flagReasons?: Option[];
        /** Names the venue in an owner's response. */
        venueName?: string;
    }>(),
    {
        canReply: false,
        canEdit: false,
        flagReasons: () => [],
        venueName: undefined,
    },
);

const dimensions = [
    { key: 'glass', label: 'Glass' },
    { key: 'lighting', label: 'Lighting' },
    { key: 'turf', label: 'Turf' },
    { key: 'facilities', label: 'Facilities' },
] as const;

// Optimistic helpful vote: flip locally, roll back if the server refuses.
const hasVoted = ref(props.review.hasVoted);
const helpfulCount = ref(props.review.helpfulCount);
const voting = ref(false);

watch(
    () => [props.review.hasVoted, props.review.helpfulCount] as const,
    ([voted, count]) => {
        hasVoted.value = voted;
        helpfulCount.value = count;
    },
);

function toggleVote(): void {
    if (voting.value) {
        return;
    }

    const previous = { voted: hasVoted.value, count: helpfulCount.value };
    hasVoted.value = !previous.voted;
    helpfulCount.value = previous.count + (previous.voted ? -1 : 1);
    voting.value = true;

    router.post(
        ToggleReviewVoteController.url(props.review.id),
        {},
        {
            preserveScroll: true,
            onError: () => {
                hasVoted.value = previous.voted;
                helpfulCount.value = previous.count;
            },
            onFinish: () => {
                voting.value = false;
            },
        },
    );
}

const flagOpen = ref(false);
const flagForm = useForm<FlagReviewFormData>({
    reason: 'spam',
    details: '',
});

function submitFlag(): void {
    flagForm.post(FlagReviewController.url(props.review.id), {
        preserveScroll: true,
        onSuccess: () => {
            flagOpen.value = false;
            flagForm.reset();
        },
    });
}
</script>

<template>
    <article class="rounded-xl border p-4">
        <header class="flex flex-wrap items-baseline justify-between gap-2">
            <div class="flex flex-wrap items-center gap-2">
                <span class="font-semibold">{{
                    review.authorDisplayName
                }}</span>
                <RatingStars :value="review.overall" size="sm" />
            </div>
            <p class="text-muted-foreground text-xs">
                <time :datetime="review.createdAt">
                    {{ formatDate(review.createdAt) }}
                </time>
                <template v-if="review.playedOn">
                    · played {{ formatDate(review.playedOn) }}
                </template>
            </p>
        </header>

        <dl class="text-muted-foreground mt-2 flex flex-wrap gap-x-4 text-xs">
            <div v-for="dimension in dimensions" :key="dimension.key">
                <dt class="inline">{{ dimension.label }}</dt>
                <dd class="inline font-semibold tabular-nums">
                    {{ review.scores[dimension.key] }}
                </dd>
            </div>
        </dl>

        <p class="mt-3 whitespace-pre-line">{{ review.body }}</p>

        <footer class="mt-3 flex flex-wrap items-center gap-2 text-xs">
            <Button
                v-if="signedIn && !review.isAuthor"
                type="button"
                size="sm"
                :variant="hasVoted ? 'secondary' : 'outline'"
                :aria-pressed="hasVoted"
                :disabled="voting"
                @click="toggleVote"
            >
                <ThumbsUp aria-hidden="true" />
                Helpful
                <span class="tabular-nums">({{ helpfulCount }})</span>
            </Button>
            <span v-else class="text-muted-foreground">
                {{ helpfulCount }} found this helpful
            </span>

            <Dialog v-if="signedIn && !review.isAuthor" v-model:open="flagOpen">
                <DialogTrigger as-child>
                    <Button type="button" size="sm" variant="ghost">
                        <Flag aria-hidden="true" />
                        Report
                    </Button>
                </DialogTrigger>
                <DialogContent>
                    <form class="space-y-4" @submit.prevent="submitFlag">
                        <DialogHeader>
                            <DialogTitle>Report this review</DialogTitle>
                            <DialogDescription>
                                Tell our moderators what is wrong. Reports are
                                anonymous to the author.
                            </DialogDescription>
                        </DialogHeader>
                        <div class="grid gap-1.5">
                            <Label :for="`flag-reason-${review.id}`"
                                >Reason</Label
                            >
                            <NativeSelect
                                :id="`flag-reason-${review.id}`"
                                v-model="flagForm.reason"
                                :options="flagReasons"
                            />
                            <InputError :message="flagForm.errors.reason" />
                        </div>
                        <div class="grid gap-1.5">
                            <Label :for="`flag-details-${review.id}`">
                                Details (optional)
                            </Label>
                            <textarea
                                :id="`flag-details-${review.id}`"
                                v-model="flagForm.details"
                                maxlength="1000"
                                rows="3"
                                class="border-input bg-background focus-visible:ring-ring/50 w-full rounded-md border px-3 py-2 text-sm shadow-xs outline-none focus-visible:ring-[3px]"
                            />
                            <InputError :message="flagForm.errors.details" />
                        </div>
                        <DialogFooter>
                            <Button
                                type="submit"
                                :disabled="flagForm.processing"
                            >
                                Send report
                            </Button>
                        </DialogFooter>
                    </form>
                </DialogContent>
            </Dialog>

            <Button v-if="canEdit" as-child size="sm" variant="ghost">
                <Link :href="reviewEdit(review.id)">Edit your review</Link>
            </Button>
        </footer>

        <blockquote
            v-if="review.reply"
            class="bg-muted mt-3 rounded-md p-3 text-sm"
        >
            <p class="flex flex-wrap items-center gap-x-2 gap-y-1">
                <template v-if="review.reply.fromOwner">
                    <Badge variant="secondary" class="gap-1">
                        <BadgeCheck aria-hidden="true" />
                        Owner
                    </Badge>
                    <span class="font-semibold">
                        Response from the owner<template v-if="venueName">
                            of {{ venueName }}</template
                        >
                    </span>
                </template>
                <span v-else class="font-semibold">
                    Response from {{ review.reply.authorDisplayName }}
                </span>
                <span class="text-muted-foreground text-xs">
                    ·
                    <time :datetime="review.reply.createdAt">
                        {{ formatDate(review.reply.createdAt) }}
                    </time>
                </span>
            </p>
            <p class="mt-1 whitespace-pre-line">{{ review.reply.body }}</p>
        </blockquote>

        <div v-if="canReply" class="mt-3">
            <ReplyForm :review-id="review.id" :reply="review.reply" />
        </div>
    </article>
</template>
