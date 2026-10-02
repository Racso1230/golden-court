<script setup lang="ts">
import { Link, router, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import { MessageSquareText } from '@lucide/vue';
import DimensionBars from '@/components/DimensionBars.vue';
import EmptyState from '@/components/EmptyState.vue';
import GoldenCourtBadge from '@/components/GoldenCourtBadge.vue';
import NativeSelect from '@/components/NativeSelect.vue';
import PageHeader from '@/components/PageHeader.vue';
import PaginationLinks from '@/components/PaginationLinks.vue';
import RatingStars from '@/components/RatingStars.vue';
import ReviewCard from '@/components/ReviewCard.vue';
import ScoreBadge from '@/components/ScoreBadge.vue';
import { Button } from '@/components/ui/button';
import { formatDateTime } from '@/lib/dates';
import { login } from '@/routes';
import { show as courtShow } from '@/routes/courts';
import { create as reviewCreate, edit as reviewEdit } from '@/routes/reviews';
import { index as venuesIndex, show as venueShow } from '@/routes/venues';
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
    /** Set when a new account must wait before reviewing: when it may. */
    reviewableFrom: string | null;
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
    | { kind: 'tooNew'; from: string }
    | { kind: 'owner' }
    | { kind: 'unavailable' };

const cta = computed<Cta>(() => {
    if (props.canReview) return { kind: 'review' };
    if (user.value === null) return { kind: 'login' };
    if (!user.value.email_verified_at) return { kind: 'verify' };
    if (props.hasReviewed) {
        return { kind: 'reviewed', reviewId: ownReview.value?.id ?? null };
    }
    if (props.reviewableFrom !== null) {
        return { kind: 'tooNew', from: props.reviewableFrom };
    }
    if (user.value.role === 'venue_owner') return { kind: 'owner' };

    return { kind: 'unavailable' };
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
    <PageHeader
        :title="court.court.name"
        :breadcrumbs="[
            { title: 'Venues', href: venuesIndex() },
            { title: court.venueName, href: venueShow(court.venueSlug) },
        ]"
        :description="`${court.court.courtTypeLabel} · ${court.court.wallTypeLabel} walls · ${court.court.surfaceLabel} · at ${court.venueName}, ${court.venueCity}`"
    >
        <template v-if="court.court.isGoldenCourt" #meta>
            <GoldenCourtBadge :city="court.venueCity" />
        </template>
    </PageHeader>

    <section
        aria-labelledby="averages-heading"
        class="bg-card grid gap-8 rounded-xl border p-6 shadow-xs md:grid-cols-[13rem_1fr_16rem] md:items-center"
    >
        <h2 id="averages-heading" class="sr-only">Scores</h2>

        <div v-if="court.court.reviewCount > 0">
            <p class="font-display text-6xl leading-none tabular-nums">
                {{ court.court.aggregateScore.toFixed(1) }}
            </p>
            <div class="mt-3">
                <RatingStars
                    :value="court.court.aggregateScore"
                    size="lg"
                    :show-value="false"
                />
            </div>
            <p class="text-muted-foreground mt-1 text-sm">
                From {{ court.court.reviewCount }}
                {{ court.court.reviewCount === 1 ? 'review' : 'reviews' }}
            </p>
        </div>
        <div v-else>
            <ScoreBadge :score="0" :review-count="0" />
            <p class="text-muted-foreground mt-1 text-sm">
                Scores appear after the first review.
            </p>
        </div>

        <DimensionBars :averages="court.averages" />

        <div
            class="bg-muted/60 rounded-lg p-4 text-sm md:self-stretch"
            aria-labelledby="cta-heading"
            role="region"
        >
            <h3 id="cta-heading" class="font-semibold">Played here?</h3>
            <div class="mt-2">
                <template v-if="cta.kind === 'review'">
                    <p class="text-muted-foreground">
                        Rate the glass, lighting, turf and facilities.
                    </p>
                    <Button as-child class="mt-3 w-full">
                        <Link :href="reviewCreate(court.court.id)"
                            >Write a review</Link
                        >
                    </Button>
                </template>
                <p
                    v-else-if="cta.kind === 'login'"
                    class="text-muted-foreground"
                >
                    <Link
                        :href="login()"
                        class="text-foreground font-medium underline"
                        >Log in</Link
                    >
                    to write a review.
                </p>
                <p
                    v-else-if="cta.kind === 'verify'"
                    class="text-muted-foreground"
                >
                    Verify your email to write a review.
                    <Link
                        :href="sendVerification()"
                        as="button"
                        class="text-foreground font-medium underline"
                    >
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
                        class="text-foreground font-medium underline"
                    >
                        Edit your review
                    </Link>
                </p>
                <p
                    v-else-if="cta.kind === 'tooNew'"
                    class="text-muted-foreground"
                    role="status"
                >
                    New accounts wait a little before writing reviews. You can
                    review this court from {{ formatDateTime(cta.from) }}.
                </p>
                <p
                    v-else-if="cta.kind === 'owner'"
                    class="text-muted-foreground"
                >
                    Venue owners cannot review courts.
                </p>
                <p v-else class="text-muted-foreground">
                    You cannot review this court.
                </p>
            </div>
        </div>
    </section>

    <section class="mt-12 space-y-5" aria-labelledby="reviews-heading">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <h2
                id="reviews-heading"
                class="text-2xl font-semibold tracking-tight"
            >
                Reviews
                <span class="text-muted-foreground font-normal tabular-nums"
                    >({{ reviews.total }})</span
                >
            </h2>
            <div
                v-if="reviews.total > 0"
                class="flex items-center gap-2 text-sm"
            >
                <label for="review-sort" class="text-muted-foreground"
                    >Sort by</label
                >
                <div class="w-44">
                    <NativeSelect
                        id="review-sort"
                        v-model="sortModel"
                        :options="sortOptions"
                    />
                </div>
            </div>
        </div>

        <EmptyState
            v-if="reviews.data.length === 0"
            title="No reviews yet"
            description="Be the first to say what this court is like to play on."
        >
            <template #icon><MessageSquareText /></template>
        </EmptyState>

        <ul v-else class="space-y-4">
            <li v-for="review in reviews.data" :key="review.id">
                <ReviewCard
                    :review="review"
                    :signed-in="user !== null"
                    :can-reply="canReply"
                    :can-edit="review.isAuthor"
                    :flag-reasons="flagReasons"
                    :venue-name="court.venueName"
                />
            </li>
        </ul>

        <PaginationLinks :links="reviews.links" />
    </section>
</template>
