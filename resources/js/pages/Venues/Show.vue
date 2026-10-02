<script setup lang="ts">
import { Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import StoreVenueClaimController from '@/actions/App/Http/Controllers/Claims/StoreVenueClaimController';
import CourtCard from '@/components/CourtCard.vue';
import InputError from '@/components/InputError.vue';
import ScoreBadge from '@/components/ScoreBadge.vue';
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
</script>

<template>
    <Link :href="venuesIndex()" class="text-sm hover:underline">
        ← All venues
    </Link>

    <header class="mt-4 flex flex-wrap items-start justify-between gap-4">
        <div class="space-y-2">
            <h1 class="text-3xl font-bold tracking-tight">{{ venue.name }}</h1>
            <ScoreBadge
                :score="venue.aggregateScore"
                :review-count="venue.reviewCount"
                size="lg"
            />
            <address class="text-muted-foreground text-sm not-italic">
                {{ venue.addressLine1
                }}<template v-if="venue.addressLine2"
                    >, {{ venue.addressLine2 }}</template
                >, {{ venue.city }} {{ venue.postcode }}
            </address>
            <p class="text-muted-foreground flex flex-wrap gap-3 text-sm">
                <a
                    v-if="venue.website"
                    :href="venue.website"
                    rel="noopener"
                    target="_blank"
                    class="hover:underline"
                    >Website</a
                >
                <span v-if="venue.phone">{{ venue.phone }}</span>
                <Badge v-if="venue.ownerDisplayName" variant="secondary">
                    Managed by {{ venue.ownerDisplayName }}
                </Badge>
            </p>
            <p v-if="venue.description" class="max-w-2xl">
                {{ venue.description }}
            </p>
        </div>

        <Dialog v-if="canClaim" v-model:open="claimOpen">
            <DialogTrigger as-child>
                <Button type="button" variant="outline">
                    Claim this venue
                </Button>
            </DialogTrigger>
            <DialogContent>
                <form class="space-y-4" @submit.prevent="submitClaim">
                    <DialogHeader>
                        <DialogTitle>Claim {{ venue.name }}</DialogTitle>
                        <DialogDescription>
                            Tell us how we can confirm you own or manage this
                            venue. Once approved you can reply to reviews as the
                            venue.
                        </DialogDescription>
                    </DialogHeader>
                    <div class="grid gap-1.5">
                        <Label for="evidence">Evidence</Label>
                        <textarea
                            id="evidence"
                            v-model="claimForm.evidence"
                            required
                            minlength="20"
                            maxlength="2000"
                            rows="4"
                            class="border-input bg-background focus-visible:ring-ring/50 w-full rounded-md border px-3 py-2 text-sm shadow-xs outline-none focus-visible:ring-[3px]"
                            placeholder="e.g. I am the club manager; my email matches the domain on our website."
                        />
                        <InputError :message="claimForm.errors.evidence" />
                    </div>
                    <DialogFooter>
                        <Button type="submit" :disabled="claimForm.processing">
                            Submit claim
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    </header>

    <section class="mt-8 space-y-4">
        <h2 class="text-2xl font-semibold">Courts ({{ venue.courtCount }})</h2>
        <ul class="grid gap-4 sm:grid-cols-2">
            <li v-for="court in venue.courts" :key="court.id">
                <CourtCard :court="court" :venue-slug="venue.slug" />
            </li>
        </ul>
    </section>
</template>
