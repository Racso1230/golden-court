<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { Building2 } from '@lucide/vue';
import EmptyState from '@/components/EmptyState.vue';
import PageHeader from '@/components/PageHeader.vue';
import StatusBadge from '@/components/StatusBadge.vue';
import { Button } from '@/components/ui/button';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { formatDate } from '@/lib/dates';
import { claimStatusTone } from '@/lib/status';
import { index as venuesIndex, show as venueShow } from '@/routes/venues';
import type { VenueClaim } from '@/types';

defineProps<{ claims: VenueClaim[] }>();
</script>

<template>
    <PageHeader
        eyebrow="Your account"
        title="My venue claims"
        description="Venues you have asked to manage. Once approved you can reply to their reviews."
    />

    <EmptyState
        v-if="claims.length === 0"
        title="You have not claimed a venue"
        description="Run a venue? Open it from the venue list and choose 'Claim this venue'."
    >
        <template #icon><Building2 /></template>
        <Button variant="outline" as-child>
            <Link :href="venuesIndex()">Browse venues</Link>
        </Button>
    </EmptyState>

    <div v-else class="bg-card overflow-hidden rounded-xl border shadow-xs">
        <Table>
            <TableHeader class="bg-muted/50">
                <TableRow>
                    <TableHead class="px-5">Venue</TableHead>
                    <TableHead>Status</TableHead>
                    <TableHead>Submitted</TableHead>
                    <TableHead class="px-5">Decision</TableHead>
                </TableRow>
            </TableHeader>
            <TableBody>
                <TableRow v-for="claim in claims" :key="claim.id">
                    <TableCell class="px-5 py-3">
                        <Link
                            :href="venueShow(claim.venueSlug)"
                            class="font-medium hover:underline"
                        >
                            {{ claim.venueName }}
                        </Link>
                    </TableCell>
                    <TableCell>
                        <StatusBadge :tone="claimStatusTone(claim.status)">
                            {{ claim.statusLabel }}
                        </StatusBadge>
                    </TableCell>
                    <TableCell class="whitespace-nowrap tabular-nums">
                        {{ formatDate(claim.submittedAt) }}
                    </TableCell>
                    <TableCell
                        class="text-muted-foreground px-5 whitespace-normal"
                    >
                        <template v-if="claim.reviewedAt">
                            {{ formatDate(claim.reviewedAt) }}
                            <template v-if="claim.rejectionReason">
                                · {{ claim.rejectionReason }}
                            </template>
                        </template>
                        <template v-else>Awaiting review</template>
                    </TableCell>
                </TableRow>
            </TableBody>
        </Table>
    </div>
</template>
