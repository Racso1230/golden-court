<script setup lang="ts">
import { Link, useForm, usePage } from '@inertiajs/vue3';
import ApproveVenueClaimController from '@/actions/App/Http/Controllers/Admin/ApproveVenueClaimController';
import RejectVenueClaimController from '@/actions/App/Http/Controllers/Admin/RejectVenueClaimController';
import ConfirmDialog from '@/components/ConfirmDialog.vue';
import { ShieldCheck } from '@lucide/vue';
import EmptyState from '@/components/EmptyState.vue';
import PageHeader from '@/components/PageHeader.vue';
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
import { formatDate } from '@/lib/dates';
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
</script>

<template>
    <div class="space-y-6">
        <PageHeader
            eyebrow="Admin"
            title="Pending venue claims"
            :description="`${claims.total} awaiting a decision`"
        />

        <EmptyState
            v-if="claims.data.length === 0"
            title="Nothing to review"
            description="The queue is clear. New items appear here as players submit them."
        >
            <template #icon><ShieldCheck /></template>
        </EmptyState>

        <div v-else class="bg-card overflow-hidden rounded-xl border shadow-xs">
            <Table>
                <TableHeader class="bg-muted/50">
                    <TableRow>
                        <TableHead class="px-5">Venue</TableHead>
                        <TableHead>Claimant</TableHead>
                        <TableHead>Evidence</TableHead>
                        <TableHead>Submitted</TableHead>
                        <TableHead
                            ><span class="sr-only">Actions</span></TableHead
                        >
                    </TableRow>
                </TableHeader>
                <TableBody>
                    <TableRow v-for="claim in claims.data" :key="claim.id">
                        <TableCell class="px-5 py-4">
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
                                            v-model="
                                                rejectForm.rejection_reason
                                            "
                                            required
                                            minlength="5"
                                            placeholder="e.g. We could not verify you manage this venue."
                                        />
                                        <InputError
                                            :message="
                                                rejectForm.errors
                                                    .rejection_reason
                                            "
                                        />
                                    </div>
                                </ConfirmDialog>
                                <InputError
                                    :message="page.props.errors.claim"
                                />
                            </div>
                        </TableCell>
                    </TableRow>
                </TableBody>
            </Table>
        </div>

        <PaginationLinks :links="claims.links" />
    </div>
</template>
