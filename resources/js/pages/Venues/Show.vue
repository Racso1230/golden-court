<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import {
    BadgeCheck,
    ExternalLink,
    Globe,
    MapPin,
    Navigation,
    Phone,
} from '@lucide/vue';
import { computed, ref } from 'vue';
import StoreVenueClaimController from '@/actions/App/Http/Controllers/Claims/StoreVenueClaimController';
import CourtCard from '@/components/CourtCard.vue';
import InputError from '@/components/InputError.vue';
import PageHeader from '@/components/PageHeader.vue';
import ScoreBadge from '@/components/ScoreBadge.vue';
import SectionCard from '@/components/SectionCard.vue';
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
import { index as venuesIndex } from '@/routes/venues';
import type { VenueClaimFormData, VenueDetail } from '@/types';

const props = defineProps<{ venue: VenueDetail; canClaim: boolean }>();

const claimOpen = ref(false);
const claimForm = useForm<VenueClaimFormData>({ evidence: '' });

function submitClaim(): void {
    claimForm.post(StoreVenueClaimController.url(props.venue.slug), {
        onSuccess: () => {
            claimOpen.value = false;
        },
    });
}

const address = computed(() =>
    [
        props.venue.addressLine1,
        props.venue.addressLine2,
        `${props.venue.city} ${props.venue.postcode}`,
    ]
        .filter((line): line is string => line !== null && line !== '')
        .join(', '),
);

const directionsUrl = computed(
    () =>
        `https://www.google.com/maps/dir/?api=1&destination=${props.venue.latitude},${props.venue.longitude}`,
);

const websiteHost = computed(() => {
    if (!props.venue.website) return null;

    try {
        return new URL(props.venue.website).host.replace(/^www\./, '');
    } catch {
        return props.venue.website;
    }
});
</script>

<template>
    <PageHeader
        :title="venue.name"
        :breadcrumbs="[{ title: 'Venues', href: venuesIndex() }]"
    >
        <template #meta>
            <div class="flex flex-wrap items-center gap-3">
                <ScoreBadge
                    :score="venue.aggregateScore"
                    :review-count="venue.reviewCount"
                    size="lg"
                />
                <Badge v-if="venue.ownerDisplayName" variant="secondary">
                    <BadgeCheck aria-hidden="true" />
                    Managed by {{ venue.ownerDisplayName }}
                </Badge>
            </div>
            <p
                class="text-muted-foreground mt-2 flex items-center gap-1.5 text-sm"
            >
                <MapPin class="size-4 shrink-0" aria-hidden="true" />
                {{ venue.city }} · {{ venue.courtCount }}
                {{ venue.courtCount === 1 ? 'court' : 'courts' }}
            </p>
        </template>

        <template #actions>
            <Button v-if="venue.website" variant="outline" as-child>
                <a :href="venue.website" rel="noopener" target="_blank">
                    <ExternalLink aria-hidden="true" />
                    Visit website
                </a>
            </Button>

            <Dialog v-if="canClaim" v-model:open="claimOpen">
                <DialogTrigger as-child>
                    <Button type="button">Claim this venue</Button>
                </DialogTrigger>
                <DialogContent>
                    <form class="space-y-4" @submit.prevent="submitClaim">
                        <DialogHeader>
                            <DialogTitle>Claim {{ venue.name }}</DialogTitle>
                            <DialogDescription>
                                Tell us how we can confirm you own or manage
                                this venue. Once approved you can reply to
                                reviews as the venue.
                            </DialogDescription>
                        </DialogHeader>
                        <div class="grid gap-1.5">
                            <Label for="evidence">Evidence</Label>
                            <Textarea
                                id="evidence"
                                v-model="claimForm.evidence"
                                required
                                minlength="20"
                                maxlength="2000"
                                rows="4"
                                placeholder="e.g. I am the club manager; my email matches the domain on our website."
                            />
                            <InputError :message="claimForm.errors.evidence" />
                        </div>
                        <DialogFooter>
                            <Button
                                type="submit"
                                :disabled="claimForm.processing"
                            >
                                Submit claim
                            </Button>
                        </DialogFooter>
                    </form>
                </DialogContent>
            </Dialog>
        </template>
    </PageHeader>

    <div class="grid gap-8 lg:grid-cols-[1fr_20rem] lg:items-start">
        <div class="space-y-10">
            <section v-if="venue.description" aria-labelledby="about-heading">
                <h2
                    id="about-heading"
                    class="text-xl font-semibold tracking-tight"
                >
                    About
                </h2>
                <p class="mt-3 max-w-2xl text-base leading-relaxed">
                    {{ venue.description }}
                </p>
            </section>

            <section aria-labelledby="courts-heading">
                <h2
                    id="courts-heading"
                    class="text-xl font-semibold tracking-tight"
                >
                    Courts
                    <span class="text-muted-foreground font-normal tabular-nums"
                        >({{ venue.courtCount }})</span
                    >
                </h2>
                <p class="text-muted-foreground mt-1 text-sm">
                    Each court is reviewed on its own. Pick one to read what
                    players thought.
                </p>
                <ul class="mt-4 grid gap-4 sm:grid-cols-2">
                    <li v-for="court in venue.courts" :key="court.id">
                        <CourtCard :court="court" :venue-slug="venue.slug" />
                    </li>
                </ul>
            </section>
        </div>

        <SectionCard
            as="div"
            title="Find the venue"
            class="lg:sticky lg:top-24"
        >
            <dl class="space-y-4 text-sm">
                <div class="flex gap-3">
                    <dt class="sr-only">Address</dt>
                    <MapPin
                        class="text-gold-600 mt-0.5 size-4 shrink-0"
                        aria-hidden="true"
                    />
                    <dd>
                        <address class="not-italic">{{ address }}</address>
                        <a
                            :href="directionsUrl"
                            rel="noopener"
                            target="_blank"
                            class="text-gold-700 mt-1 inline-flex items-center gap-1 font-medium hover:underline"
                        >
                            <Navigation class="size-3.5" aria-hidden="true" />
                            Get directions
                        </a>
                    </dd>
                </div>
                <div v-if="venue.phone" class="flex gap-3">
                    <dt class="sr-only">Phone</dt>
                    <Phone
                        class="text-gold-600 mt-0.5 size-4 shrink-0"
                        aria-hidden="true"
                    />
                    <dd>
                        <a
                            :href="`tel:${venue.phone.replace(/\s+/g, '')}`"
                            class="hover:underline"
                            >{{ venue.phone }}</a
                        >
                    </dd>
                </div>
                <div v-if="venue.website" class="flex gap-3">
                    <dt class="sr-only">Website</dt>
                    <Globe
                        class="text-gold-600 mt-0.5 size-4 shrink-0"
                        aria-hidden="true"
                    />
                    <dd class="min-w-0">
                        <a
                            :href="venue.website"
                            rel="noopener"
                            target="_blank"
                            class="block truncate hover:underline"
                            >{{ websiteHost }}</a
                        >
                    </dd>
                </div>
            </dl>
        </SectionCard>
    </div>
</template>
