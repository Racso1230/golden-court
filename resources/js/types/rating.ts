/**
 * A single 1–5 score, mirroring the Rating value object's range.
 */
export type Rating = 1 | 2 | 3 | 4 | 5;

export const RATINGS: readonly Rating[] = [1, 2, 3, 4, 5];

export function isRating(value: unknown): value is Rating {
    return typeof value === 'number' && RATINGS.includes(value as Rating);
}
