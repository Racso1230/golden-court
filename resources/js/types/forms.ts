import type { Rating } from '@/types/rating';

/**
 * Request payloads the frontend sends. These describe what the Form
 * Requests accept (snake_case), not what the backend returns.
 */
export type ReviewFormData = {
    court_id: number;
    glass: Rating | null;
    lighting: Rating | null;
    turf: Rating | null;
    facilities: Rating | null;
    body: string;
    /** Empty string when unset; the backend converts it to null. */
    played_on: string;
};

export type FlagReviewFormData = {
    reason: App.Domain.Moderation.Enums.FlagReason;
    details: string;
};

export type ReplyFormData = {
    body: string;
};

export type VenueClaimFormData = {
    evidence: string;
};
