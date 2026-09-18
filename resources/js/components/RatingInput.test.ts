import { mount } from '@vue/test-utils';
import { describe, expect, it } from 'vitest';
import RatingInput from '@/components/RatingInput.vue';
import type { Rating } from '@/types/rating';

function mountInput(modelValue: Rating | null = null, error?: string) {
    return mount(RatingInput, {
        props: {
            label: 'Glass',
            hint: 'Clarity and cleanliness',
            error,
            modelValue,
        },
    });
}

describe('RatingInput', () => {
    it('renders five labelled radio options in a labelled group', () => {
        const wrapper = mountInput();
        const radios = wrapper.findAll('[role="radio"]');

        expect(wrapper.find('[role="radiogroup"]').exists()).toBe(true);
        expect(radios).toHaveLength(5);
        expect(radios.map((radio) => radio.attributes('aria-label'))).toEqual([
            '1 out of 5',
            '2 out of 5',
            '3 out of 5',
            '4 out of 5',
            '5 out of 5',
        ]);
        expect(wrapper.text()).toContain('Not rated');
    });

    it('emits the clicked rating', async () => {
        const wrapper = mountInput();

        await wrapper.findAll('[role="radio"]')[2]?.trigger('click');

        expect(wrapper.emitted('update:modelValue')?.[0]).toEqual([3]);
    });

    it('marks the current value checked and only it is tabbable', () => {
        const wrapper = mountInput(4);
        const radios = wrapper.findAll('[role="radio"]');

        expect(radios[3]?.attributes('aria-checked')).toBe('true');
        expect(radios[3]?.attributes('tabindex')).toBe('0');
        expect(radios[0]?.attributes('tabindex')).toBe('-1');
        expect(wrapper.text()).toContain('4 / 5');
    });

    it('moves the selection with the arrow, Home and End keys', async () => {
        const wrapper = mountInput(2);
        const radios = wrapper.findAll('[role="radio"]');

        await radios[1]?.trigger('keydown', { key: 'ArrowRight' });
        await radios[1]?.trigger('keydown', { key: 'ArrowLeft' });
        await radios[1]?.trigger('keydown', { key: 'End' });
        await radios[1]?.trigger('keydown', { key: 'Home' });

        expect(wrapper.emitted('update:modelValue')).toEqual([
            [3],
            [1],
            [5],
            [1],
        ]);
    });

    it('exposes a validation error to assistive tech', () => {
        const wrapper = mountInput(null, 'Please rate the glass.');

        expect(
            wrapper.find('[role="radiogroup"]').attributes('aria-invalid'),
        ).toBe('true');
        expect(wrapper.find('[role="alert"]').text()).toBe(
            'Please rate the glass.',
        );
    });
});
