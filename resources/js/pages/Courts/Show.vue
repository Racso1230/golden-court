<script setup lang="ts">
import { Link, router, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import DimensionBars from '@/components/DimensionBars.vue';
import GoldenCourtBadge from '@/components/GoldenCourtBadge.vue';
import NativeSelect from '@/components/NativeSelect.vue';
import PaginationLinks from '@/components/PaginationLinks.vue';
import ReviewCard from '@/components/ReviewCard.vue';
import ScoreBadge from '@/components/ScoreBadge.vue';
import { Button } from '@/components/ui/button';
import { login } from '@/routes';
import { show as courtShow } from '@/routes/courts';
import { create as reviewCreate, edit as reviewEdit } from '@/routes/reviews';
import { show as venueShow } from '@/routes/venues';
import { send as sendVerification } from '@/routes/verification';
import type {
    CourtDetail,
    Option,
    Paginated,
    Review,
    ReviewSort,
} from '@/types';

const props = defineProps<{
    court: CourtDetail;
    reviews: Paginated<Review>;
    sort: ReviewSort;
    sortOptions: Option[];
    flagReasons: Option[];
    canReview: boolean;
    hasReviewed: boolean;
    canReply: boolean;
}>();

const page = usePage();
// Guests have no user even though the shared type says otherwise.
const user = computed(() => page.props.auth.user ?? null);

const ownReview = computed(
    () => props.reviews.data.find((review) => review.isAuthor) ?? null,
);

type Cta =
    | { kind: 'review' }
    | { kind: 'login' }
    | { kind: 'verify' }
    | { kind: 'reviewed'; reviewId: number | null }
    | { kind: 'owner' };

const cta = computed<Cta>(() => {
    if (props.canReview) return { kind: 'review' };
    if (user.value === null) return { kind: 'login' };
    if (!user.value.email_verified_at) return { kind: 'verify' };
    if (props.hasReviewed) {
        return { kind: 'reviewed', reviewId: ownReview.value?.id ?? null };
    }

    return { kind: 'owner' };
});

const sortModel = computed({
    get: () => props.sort,
    set: (value: string) => {
        router.get(
            courtShow.url({
                venue: props.court.venueSlug,
                court: props.court.court.slug,
            }),
            { sort: value },
            { preserveScroll: true },
        );
    },
});
</script>

<template>
    <Link :href="venueShow(court.venueSlug)" class="text-sm hover:underline">
        ← {{ court.venueName }}
    </Link>

    <header class="mt-4 flex flex-wrap items-start justify-between gap-4">
        <div class="space-y-2">
            <div class="flex flex-wrap items-center gap-3">
                <h1 class="text-3xl font-bold tracking-tight">
                    {{ court.court.name }}
                </h1>
                <GoldenCourtBadge
                    v-if="court.court.isGoldenCourt"
                    :city="court.venueCity"
                />
            </div>
            <p class="text-muted-foreground text-sm">
                {{ court.court.courtTypeLabel }} ·
                {{ court.court.wallTypeLabel }} ·
                {{ court.court.surfaceLabel }}
            </p>
            <ScoreBadge
                :score="court.court.aggregateScore"
                :review-count="court.court.reviewCount"
                size="lg"
            />
        </div>

        <div class="text-sm">
            <Button v-if="cta.kind === 'review'" as-child>
                <Link :href="reviewCreate(court.court.id)">Write a review</Link>
            </Button>
            <p v-else-if="cta.kind === 'login'" class="text-muted-foreground">
                <Link :href="login()" class="underline">Log in</Link>
                to write a review.
            </p>
            <p v-else-if="cta.kind === 'verify'" class="text-muted-foreground">
                Verify your email to write a review.
                <Link :href="sendVerification()" as="button" class="underline">
                    Resend the link
                </Link>
            </p>
            <p
                v-else-if="cta.kind === 'reviewed'"
                class="text-muted-foreground"
            >
                You have reviewed this court.
                <Link
                    v-if="cta.reviewId !== null"
                    :href="reviewEdit(cta.reviewId)"
                    class="underline"
                >
                    Edit your review
                </Link>
            </p>
            <p v-else class="text-muted-foreground">
                Venue owners cannot review courts.
            </p>
        </div>
    </header>

    <section class="mt-6" aria-labelledby="averages-heading">
        <h2 id="averages-heading" class="sr-only">Average scores</h2>
        <DimensionBars :averages="court.averages" />
    </section>

    <section class="mt-8 space-y-4" aria-labelledby="reviews-heading">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <h2 id="reviews-heading" class="text-2xl font-semibold">
                Reviews ({{ reviews.total }})
            </h2>
            <label class="flex items-center gap-2 text-sm">
                Sort
                <NativeSelect
                    id="review-sort"
                    v-model="sortModel"
                    :options="sortOptions"
                    class="w-auto"
                />
            </label>
        </div>

        <p v-if="reviews.data.length === 0" class="text-muted-foreground">
            No reviews yet.
        </p>

        <ul class="space-y-4">
            <li v-for="review in reviews.data" :key="review.id">
                <ReviewCard
                    :review="review"
                    :signed-in="user !== null"
                    :can-reply="canReply"
                    :can-edit="review.isAuthor"
                    :flag-reasons="flagReasons"
                />
            </li>
        </ul>

        <PaginationLinks :links="reviews.links" />
    </section>
</template>
