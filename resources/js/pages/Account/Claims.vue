<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import Heading from '@/components/Heading.vue';
import { Badge } from '@/components/ui/badge';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { formatDate } from '@/lib/dates';
import { index as venuesIndex, show as venueShow } from '@/routes/venues';
import type { ClaimStatus, VenueClaim } from '@/types';

defineProps<{ claims: VenueClaim[] }>();

function statusVariant(
    status: ClaimStatus,
): 'default' | 'secondary' | 'destructive' {
    switch (status) {
        case 'approved':
            return 'default';
        case 'pending':
            return 'secondary';
        case 'rejected':
            return 'destructive';
    }
}
</script>

<template>
    <Head title="My claims" />

    <div class="flex flex-col space-y-6 p-4">
        <Heading
            title="My venue claims"
            description="Claims you have made to manage a venue"
        />

        <p v-if="claims.length === 0" class="text-muted-foreground">
            You have not claimed a venue. Open a venue from the
            <Link :href="venuesIndex()" class="underline">venue list</Link>
            and use "Claim this venue".
        </p>

        <Table v-else>
            <TableHeader>
                <TableRow>
                    <TableHead>Venue</TableHead>
                    <TableHead>Status</TableHead>
                    <TableHead>Submitted</TableHead>
                    <TableHead>Decision</TableHead>
                </TableRow>
            </TableHeader>
            <TableBody>
                <TableRow v-for="claim in claims" :key="claim.id">
                    <TableCell>
                        <Link
                            :href="venueShow(claim.venueSlug)"
                            class="font-medium hover:underline"
                        >
                            {{ claim.venueName }}
                        </Link>
                    </TableCell>
                    <TableCell>
                        <Badge :variant="statusVariant(claim.status)">
                            {{ claim.statusLabel }}
                        </Badge>
                    </TableCell>
                    <TableCell class="whitespace-nowrap">
                        {{ formatDate(claim.submittedAt) }}
                    </TableCell>
                    <TableCell class="text-muted-foreground">
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
