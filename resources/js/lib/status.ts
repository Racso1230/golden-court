import type { ClaimStatus, ReviewStatus } from '@/types';

/** How a status is coloured; the label always comes from the server. */
export type StatusTone = 'success' | 'warning' | 'danger' | 'neutral';

export function reviewStatusTone(status: ReviewStatus): StatusTone {
    switch (status) {
        case 'published':
            return 'success';
        case 'pending':
        case 'flagged':
            return 'warning';
        case 'removed':
            return 'danger';
    }
}

export function claimStatusTone(status: ClaimStatus): StatusTone {
    switch (status) {
        case 'approved':
            return 'success';
        case 'pending':
            return 'warning';
        case 'rejected':
            return 'danger';
    }
}
