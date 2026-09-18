<script setup lang="ts">
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import ApproveVenueClaimController from '@/actions/App/Http/Controllers/Admin/ApproveVenueClaimController';
import RejectVenueClaimController from '@/actions/App/Http/Controllers/Admin/RejectVenueClaimController';
import ConfirmDialog from '@/components/ConfirmDialog.vue';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import PaginationLinks from '@/components/PaginationLinks.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { show as venueShow } from '@/routes/venues';
import type { Paginated, VenueClaim } from '@/types';

defineProps<{ claims: Paginated<VenueClaim> }>();

// Decision errors come back under a `claim` key that neither form owns.
const page = usePage();

const approveForm = useForm({});
const rejectForm = useForm<{ rejection_reason: string }>({
    rejection_reason: '',
});

function approve(claim: VenueClaim): void {
    approveForm.post(ApproveVenueClaimController.url(claim.id), {
        preserveScroll: true,
    });
}

function reject(claim: VenueClaim): void {
    rejectForm.post(RejectVenueClaimController.url(claim.id), {
        preserveScroll: true,
        onSuccess: () => rejectForm.reset(),
    });
}

function formatDate(value: string): string {
    return new Date(value).toLocaleDateString('en-GB', {
        day: 'numeric',
        month: 'short',
        year: 'numeric',
    });
}
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

        <Table v-else>
            <TableHeader>
                <TableRow>
                    <TableHead>Venue</TableHead>
                    <TableHead>Claimant</TableHead>
                    <TableHead>Evidence</TableHead>
                    <TableHead>Submitted</TableHead>
                    <TableHead><span class="sr-only">Actions</span></TableHead>
                </TableRow>
            </TableHeader>
            <TableBody>
                <TableRow v-for="claim in claims.data" :key="claim.id">
                    <TableCell>
                        <Link
                            :href="venueShow(claim.venueSlug)"
                            class="font-medium hover:underline"
                        >
                            {{ claim.venueName }}
                        </Link>
                    </TableCell>
                    <TableCell>
                        {{ claim.claimantDisplayName }}
                        <span class="text-muted-foreground block text-xs">
                            {{ claim.claimantEmail }}
                        </span>
                    </TableCell>
                    <TableCell class="max-w-md whitespace-pre-line">
                        {{ claim.evidence }}
                    </TableCell>
                    <TableCell class="whitespace-nowrap">
                        {{ formatDate(claim.submittedAt) }}
                    </TableCell>
                    <TableCell>
                        <div class="flex flex-col items-start gap-2">
                            <Button
                                type="button"
                                size="sm"
                                :disabled="approveForm.processing"
                                @click="approve(claim)"
                            >
                                Approve
                            </Button>
                            <ConfirmDialog
                                title="Reject this claim?"
                                :description="`${claim.claimantDisplayName} will be told the claim on ${claim.venueName} was not approved, with your reason.`"
                                confirm-label="Reject claim"
                                :processing="rejectForm.processing"
                                @confirm="reject(claim)"
                            >
                                <template #trigger>
                                    <Button
                                        type="button"
                                        size="sm"
                                        variant="outline"
                                    >
                                        Reject
                                    </Button>
                                </template>
                                <div class="grid gap-1.5">
                                    <Label :for="`reason-${claim.id}`">
                                        Reason
                                    </Label>
                                    <Input
                                        :id="`reason-${claim.id}`"
                                        v-model="rejectForm.rejection_reason"
                                        required
                                        minlength="5"
                                        placeholder="e.g. We could not verify you manage this venue."
                                    />
                                    <InputError
                                        :message="
                                            rejectForm.errors.rejection_reason
                                        "
                                    />
                                </div>
                            </ConfirmDialog>
                            <InputError :message="page.props.errors.claim" />
                        </div>
                    </TableCell>
                </TableRow>
            </TableBody>
        </Table>

        <PaginationLinks :links="claims.links" />
    </div>
</template>
