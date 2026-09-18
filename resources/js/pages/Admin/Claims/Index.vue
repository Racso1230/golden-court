<script setup lang="ts">
import { Form, Head, Link } from '@inertiajs/vue3';
import ApproveVenueClaimController from '@/actions/App/Http/Controllers/Admin/ApproveVenueClaimController';
import RejectVenueClaimController from '@/actions/App/Http/Controllers/Admin/RejectVenueClaimController';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import PaginationLinks from '@/components/PaginationLinks.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { show as venueShow } from '@/routes/venues';
import type { Paginated } from '@/types';

// Minimal shape for this phase; generated Data types arrive in Phase 7.
type Claim = {
    id: number;
    venueName: string;
    venueSlug: string;
    claimantDisplayName: string;
    claimantEmail: string;
    evidence: string;
    submittedAt: string;
};

defineProps<{ claims: Paginated<Claim> }>();
</script>

<template>
    <Head title="Pending claims" />

    <div class="flex flex-col space-y-6 p-4">
        <Heading
            title="Pending venue claims"
            :description="`${claims.total} awaiting a decision`"
        />

        <p v-if="claims.data.length === 0" class="text-muted-foreground">
            Nothing to review.
        </p>

        <ul class="space-y-4">
            <li
                v-for="claim in claims.data"
                :key="claim.id"
                class="space-y-3 rounded-xl border p-4"
            >
                <div
                    class="flex flex-wrap items-baseline justify-between gap-2"
                >
                    <Link
                        :href="venueShow(claim.venueSlug)"
                        class="font-semibold hover:underline"
                    >
                        {{ claim.venueName }}
                    </Link>
                    <p class="text-muted-foreground text-xs">
                        {{ claim.claimantDisplayName }} ·
                        {{ claim.claimantEmail }} ·
                        {{ new Date(claim.submittedAt).toLocaleDateString() }}
                    </p>
                </div>
                <p class="text-sm whitespace-pre-line">{{ claim.evidence }}</p>

                <div class="flex flex-wrap items-start gap-3">
                    <Form
                        v-bind="ApproveVenueClaimController.form(claim.id)"
                        v-slot="{ processing, errors }"
                    >
                        <Button type="submit" :disabled="processing">
                            Approve
                        </Button>
                        <InputError :message="errors.claim" />
                    </Form>

                    <Form
                        v-bind="RejectVenueClaimController.form(claim.id)"
                        class="flex flex-1 flex-wrap items-start gap-2"
                        v-slot="{ processing, errors }"
                    >
                        <div class="min-w-64 flex-1">
                            <Input
                                name="rejection_reason"
                                placeholder="Reason for rejection"
                                required
                                minlength="5"
                            />
                            <InputError :message="errors.rejection_reason" />
                            <InputError :message="errors.claim" />
                        </div>
                        <Button
                            type="submit"
                            variant="outline"
                            :disabled="processing"
                        >
                            Reject
                        </Button>
                    </Form>
                </div>
            </li>
        </ul>

        <PaginationLinks :links="claims.links" />
    </div>
</template>
