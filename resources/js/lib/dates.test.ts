import { describe, expect, it } from 'vitest';
import { formatDate, formatDateTime } from '@/lib/dates';

describe('formatDate', () => {
    it('renders an instant in London time whatever the machine time zone', () => {
        // 23:30 UTC on 30 June is 00:30 BST on 1 July.
        expect(formatDate('2026-06-30T23:30:00Z')).toBe('1 Jul 2026');
    });

    it('can leave the year out', () => {
        expect(formatDate('2026-06-30T23:30:00Z', { withYear: false })).toBe(
            '1 Jul',
        );
    });

    it('treats a date-only value as a calendar date', () => {
        expect(formatDate('2026-11-18')).toBe('18 Nov 2026');
    });

    it('is stable across repeated calls', () => {
        expect(formatDate('2026-01-05T12:00:00Z')).toBe(
            formatDate('2026-01-05T12:00:00Z'),
        );
    });
});

describe('formatDateTime', () => {
    it('includes the time in 24-hour London time', () => {
        expect(formatDateTime('2026-06-30T23:30:00Z')).toBe(
            '1 Jul 2026, 00:30',
        );
    });
});
