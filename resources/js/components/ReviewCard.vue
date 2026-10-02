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
import { Textarea } from '@/components/ui/textarea';
import { useInitials } from '@/composables/useInitials';
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

const { getInitials } = useInitials();

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
    <article class="bg-card rounded-xl border p-5 shadow-xs">
        <header class="flex flex-wrap items-start justify-between gap-3">
            <div class="flex items-center gap-3">
                <span
                    class="bg-gold-100 text-gold-900 flex size-9 shrink-0 items-center justify-center rounded-full text-xs font-semibold"
                    aria-hidden="true"
                    >{{ getInitials(review.authorDisplayName) }}</span
                >
                <div>
                    <p class="text-sm font-semibold">
                        {{ review.authorDisplayName }}
                    </p>
                    <RatingStars :value="review.overall" size="sm" />
                </div>
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

        <dl class="mt-3 flex flex-wrap gap-1.5 text-xs">
            <div
                v-for="dimension in dimensions"
                :key="dimension.key"
                class="bg-secondary flex gap-1 rounded-md px-2 py-0.5"
            >
                <dt class="text-muted-foreground">{{ dimension.label }}</dt>
                <dd class="font-semibold tabular-nums">
                    {{ review.scores[dimension.key] }}
                </dd>
            </div>
        </dl>

        <p class="mt-3 text-base leading-relaxed whitespace-pre-line">
            {{ review.body }}
        </p>

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
                            <Textarea
                                :id="`flag-details-${review.id}`"
                                v-model="flagForm.details"
                                maxlength="1000"
                                rows="3"
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
            class="border-gold-400 bg-gold-50/60 mt-4 rounded-lg border-l-2 p-4 text-sm"
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
