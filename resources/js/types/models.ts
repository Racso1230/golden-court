/**
 * Short aliases for the types generated from the PHP Data classes and enums
 * (see generated.d.ts). Nothing here defines a shape of its own; the source
 * of truth is always the PHP class.
 */
export type VenueSummary = App.Domain.Venues.Data.VenueSummaryData;
export type VenueDetail = App.Domain.Venues.Data.VenueDetailData;
export type VenueSearchCriteria = App.Domain.Venues.Data.VenueSearchCriteria;
export type VenueSort = App.Domain.Venues.Enums.VenueSort;
export type Coordinates = App.Domain.Venues.ValueObjects.Coordinates;

export type CourtSummary = App.Domain.Courts.Data.CourtSummaryData;
export type CourtDetail = App.Domain.Courts.Data.CourtDetailData;
export type CourtType = App.Domain.Courts.Enums.CourtType;
export type WallType = App.Domain.Courts.Enums.WallType;
export type Surface = App.Domain.Courts.Enums.Surface;

export type Review = App.Domain.Reviews.Data.ReviewData;
export type ReviewReply = App.Domain.Reviews.Data.ReviewReplyData;
export type RecentReview = App.Domain.Reviews.Data.RecentReviewData;
export type DimensionAverages = App.Domain.Reviews.Data.DimensionAveragesData;
export type ReviewStatus = App.Domain.Reviews.Enums.ReviewStatus;
export type ReviewSort = App.Domain.Reviews.Enums.ReviewSort;

export type Option = App.Domain.Shared.Data.OptionData;

export type FlagReason = App.Domain.Moderation.Enums.FlagReason;
export type ReviewFlag = App.Domain.Moderation.Data.ReviewFlagData;
export type ModerationReview = App.Domain.Moderation.Data.ModerationReviewData;
export type ModerationCounts = App.Domain.Moderation.Data.ModerationCountsData;
export type ModerationLogEntry = App.Domain.Moderation.Data.ModerationLogData;

export type VenueClaim = App.Domain.Claims.Data.VenueClaimData;
export type ClaimStatus = App.Domain.Claims.Enums.ClaimStatus;
