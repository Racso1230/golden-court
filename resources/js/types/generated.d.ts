declare namespace App {
namespace Domain {
namespace Claims {
namespace Data {
export type SubmitVenueClaimData = {
evidence: string,
};
export type VenueClaimData = {
id: number,
status: App.Domain.Claims.Enums.ClaimStatus,
statusLabel: string,
venueId: number,
venueName: string,
venueSlug: string,
claimantDisplayName: string,
claimantEmail: string,
evidence: string,
submittedAt: string,
reviewedAt: string | null,
rejectionReason: string | null,
};
}
namespace Enums {
export type ClaimStatus = 'pending' | 'approved' | 'rejected';
}
}
namespace Courts {
namespace Data {
export type CourtDetailData = {
court: App.Domain.Courts.Data.CourtSummaryData,
venueId: number,
venueName: string,
venueSlug: string,
venueCity: string,
venueAddress: App.Domain.Venues.Data.PostalAddressData,
venueCoordinates: App.Domain.Venues.ValueObjects.Coordinates,
venueWebsite: string | null,
averages: App.Domain.Reviews.Data.DimensionAveragesData,
};
export type CourtSummaryData = {
id: number,
name: string,
slug: string,
courtType: App.Domain.Courts.Enums.CourtType,
courtTypeLabel: string,
wallType: App.Domain.Courts.Enums.WallType,
wallTypeLabel: string,
surface: App.Domain.Courts.Enums.Surface,
surfaceLabel: string,
aggregateScore: number,
reviewCount: number,
isGoldenCourt: boolean,
};
}
namespace Enums {
export type CourtType = 'indoor' | 'outdoor' | 'covered';
export type Surface = 'artificial_grass' | 'carpet' | 'concrete' | 'other';
export type WallType = 'panoramic' | 'classic';
}
}
namespace Moderation {
namespace Data {
export type FlagReviewData = {
reason: App.Domain.Moderation.Enums.FlagReason,
details: string | null,
};
export type ModerationCountsData = {
pendingClaims: number,
flaggedReviews: number,
pendingReviews: number,
};
export type ModerationLogData = {
id: number,
action: App.Domain.Moderation.Enums.ModerationAction,
actionLabel: string,
actorLabel: string,
actorDisplayName: string | null,
details: Record<string, string | number | boolean | null>,
createdAt: string,
};
export type ModerationReviewData = {
review: App.Domain.Reviews.Data.ReviewData,
statusLabel: string,
authorEmail: string,
courtName: string,
courtSlug: string,
venueName: string,
venueSlug: string,
flags: App.Domain.Moderation.Data.ReviewFlagData[],
unresolvedFlagCount: number,
};
export type ReviewFlagData = {
id: number,
reason: App.Domain.Moderation.Enums.FlagReason,
reasonLabel: string,
details: string | null,
reporterDisplayName: string,
createdAt: string,
resolvedAt: string | null,
};
}
namespace Enums {
export type FlagReason = 'spam' | 'offensive' | 'not_a_review' | 'conflict_of_interest' | 'other';
export type ModerationAction = 'review_status_changed' | 'review_flags_resolved' | 'claim_approved' | 'claim_rejected';
export type ModerationSubject = 'review' | 'claim';
}
}
namespace Reviews {
namespace Data {
export type DimensionAveragesData = {
glass: number | null,
lighting: number | null,
turf: number | null,
facilities: number | null,
};
export type OwnReviewData = {
review: App.Domain.Reviews.Data.ReviewData,
statusLabel: string,
canEdit: boolean,
courtName: string,
courtSlug: string,
venueName: string,
venueSlug: string,
};
export type RecentReviewData = {
id: number,
overall: number,
excerpt: string,
authorDisplayName: string,
courtName: string,
courtSlug: string,
venueName: string,
venueSlug: string,
createdAt: string,
};
export type ReplyToReviewData = {
body: string,
};
export type ReviewData = {
id: number,
courtId: number,
scores: {
glass: number,
lighting: number,
turf: number,
facilities: number,
},
overall: number,
body: string,
status: App.Domain.Reviews.Enums.ReviewStatus,
authorDisplayName: string,
playedOn: string | null,
createdAt: string,
updatedAt: string,
helpfulCount: number,
hasVoted: boolean,
isAuthor: boolean,
reply: App.Domain.Reviews.Data.ReviewReplyData | null,
};
export type ReviewReplyData = {
id: number,
body: string,
authorDisplayName: string,
fromOwner: boolean,
createdAt: string,
};
export type SubmitReviewData = {
courtId: number,
glass: number,
lighting: number,
turf: number,
facilities: number,
body: string,
playedOn: string | null,
};
export type UpdateReviewData = {
glass: number,
lighting: number,
turf: number,
facilities: number,
body: string,
playedOn: string | null,
};
}
namespace Enums {
export type AggregationStrategy = 'simple' | 'bayesian';
export type ReviewSort = 'recent' | 'helpful' | 'highest' | 'lowest';
export type ReviewStatus = 'pending' | 'published' | 'flagged' | 'removed';
}
}
namespace Shared {
namespace Data {
export type OptionData = {
value: string,
label: string,
};
}
}
namespace Users {
namespace Data {
export type NotificationData = {
id: string,
message: string,
url: string | null,
readAt: string | null,
createdAt: string,
};
export type NotificationsSummaryData = {
unreadCount: number,
items: App.Domain.Users.Data.NotificationData[],
};
}
namespace Enums {
export type Role = 'player' | 'venue_owner' | 'admin';
}
}
namespace Venues {
namespace Data {
export type PostalAddressData = {
line1: string,
line2: string | null,
city: string,
postcode: string,
countryCode: string,
};
export type VenueDetailData = {
id: number,
name: string,
slug: string,
description: string | null,
addressLine1: string,
addressLine2: string | null,
city: string,
postcode: string,
countryCode: string,
latitude: number,
longitude: number,
website: string | null,
phone: string | null,
aggregateScore: number,
reviewCount: number,
courtCount: number,
ownerDisplayName: string | null,
courts: App.Domain.Courts.Data.CourtSummaryData[],
};
export type VenueSearchCriteria = {
term: string | null,
city: string | null,
near: App.Domain.Venues.ValueObjects.Coordinates | null,
radiusKm: number,
courtType: App.Domain.Courts.Enums.CourtType | null,
wallType: App.Domain.Courts.Enums.WallType | null,
surface: App.Domain.Courts.Enums.Surface | null,
minScore: number | null,
sort: App.Domain.Venues.Enums.VenueSort,
page: number,
};
export type VenueSummaryData = {
id: number,
name: string,
slug: string,
city: string,
aggregateScore: number,
reviewCount: number,
courtCount: number,
distanceKm: number | null,
hasGoldenCourt: boolean,
};
}
namespace Enums {
export type VenueSort = 'score' | 'reviews' | 'distance' | 'name';
}
namespace ValueObjects {
export type Coordinates = {
readonly latitude: number,
readonly longitude: number,
};
}
}
}
}
