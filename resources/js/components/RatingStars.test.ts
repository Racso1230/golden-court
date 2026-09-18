import { mount } from '@vue/test-utils';
import { describe, expect, it } from 'vitest';
import RatingStars from '@/components/RatingStars.vue';

describe('RatingStars', () => {
    it('announces the value as text and fills the stars proportionally', () => {
        const wrapper = mount(RatingStars, { props: { value: 3.5 } });

        expect(wrapper.attributes('aria-label')).toBe('3.5 out of 5');
        expect(wrapper.text()).toContain('3.5');
        expect(wrapper.find('.text-amber-500').attributes('style')).toContain(
            'width: 70%',
        );
    });

    it('clamps out-of-range values', () => {
        expect(
            mount(RatingStars, { props: { value: 7 } }).attributes(
                'aria-label',
            ),
        ).toBe('5.0 out of 5');
        expect(
            mount(RatingStars, { props: { value: -1 } }).attributes(
                'aria-label',
            ),
        ).toBe('0.0 out of 5');
    });

    it('can hide the numeric label while keeping the accessible name', () => {
        const wrapper = mount(RatingStars, {
            props: { value: 4.2, showValue: false },
        });

        expect(wrapper.text()).not.toContain('4.2');
        expect(wrapper.attributes('aria-label')).toBe('4.2 out of 5');
    });
});
