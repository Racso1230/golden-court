import type { AxeMatchers } from 'vitest-axe/matchers';

declare module 'vitest' {
    // Vitest declares `Assertion<T = any>`; interface merging requires the
    // same type parameter and default, so `any` is unavoidable here.
    // eslint-disable-next-line @typescript-eslint/no-explicit-any
    interface Assertion<T = any> extends AxeMatchers {}
    interface AsymmetricMatchersContaining extends AxeMatchers {}
}
