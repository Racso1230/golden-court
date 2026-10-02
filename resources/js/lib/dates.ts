/**
 * Date formatting for templates.
 *
 * Every formatter pins the locale and the time zone so the server and the
 * browser render identical text: `toLocaleDateString()` without a time zone
 * would otherwise cause a hydration mismatch for anything posted near
 * midnight. Date-only values (YYYY-MM-DD) are read as calendar dates, not as
 * instants, so they never shift by a day either.
 */
const LOCALE = 'en-GB';

export const DISPLAY_TIME_ZONE = 'Europe/London';

const DATE_ONLY = /^\d{4}-\d{2}-\d{2}$/;

const formatters = new Map<string, Intl.DateTimeFormat>();

function formatter(
    options: Intl.DateTimeFormatOptions,
    timeZone: string,
): Intl.DateTimeFormat {
    const key = `${timeZone}:${JSON.stringify(options)}`;
    let instance = formatters.get(key);

    if (!instance) {
        instance = new Intl.DateTimeFormat(LOCALE, { ...options, timeZone });
        formatters.set(key, instance);
    }

    return instance;
}

function parse(value: string): { date: Date; timeZone: string } {
    return DATE_ONLY.test(value)
        ? { date: new Date(`${value}T00:00:00Z`), timeZone: 'UTC' }
        : { date: new Date(value), timeZone: DISPLAY_TIME_ZONE };
}

/** "18 Sep 2026", or "18 Sep" without the year. */
export function formatDate(
    value: string,
    { withYear = true }: { withYear?: boolean } = {},
): string {
    const { date, timeZone } = parse(value);
    const options: Intl.DateTimeFormatOptions = {
        day: 'numeric',
        month: 'short',
    };

    if (withYear) {
        options.year = 'numeric';
    }

    return formatter(options, timeZone).format(date);
}

/** "18 Sep 2026, 14:05" in 24-hour time. */
export function formatDateTime(value: string): string {
    const { date, timeZone } = parse(value);

    return formatter(
        {
            day: 'numeric',
            month: 'short',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit',
        },
        timeZone,
    ).format(date);
}
