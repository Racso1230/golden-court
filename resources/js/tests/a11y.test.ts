import { mount } from '@vue/test-utils';
import { describe, expect, it } from 'vitest';
import { axe } from 'vitest-axe';
import DimensionBars from '@/components/DimensionBars.vue';
import NativeSelect from '@/components/NativeSelect.vue';
import RatingInput from '@/components/RatingInput.vue';
import RatingStars from '@/components/RatingStars.vue';
import ScoreBadge from '@/components/ScoreBadge.vue';

// Components are mounted on their own, without page landmarks, so the
// "content must be inside a region" rule does not apply here.
const options = { rules: { region: { enabled: false } } };

describe('accessibility of the shared components', () => {
    it('RatingInput is a labelled radio group', async () => {
        const wrapper = mount(RatingInput, {
            props: { label: 'Glass', hint: 'Clarity', modelValue: 3 },
        });

        expect(await axe(wrapper.element, options)).toHaveNoViolations();
    });

    it('RatingInput with an error still passes', async () => {
        const wrapper = mount(RatingInput, {
            props: {
                label: 'Glass',
                error: 'Please rate the glass.',
                modelValue: null,
            },
        });

        expect(await axe(wrapper.element, options)).toHaveNoViolations();
    });

    it('RatingStars exposes its value as text', async () => {
        const wrapper = mount(RatingStars, { props: { value: 4.2 } });

        expect(await axe(wrapper.element, options)).toHaveNoViolations();
    });

    it('ScoreBadge passes with and without reviews', async () => {
        const scored = mount(ScoreBadge, {
            props: { score: 4.1, reviewCount: 3 },
        });
        const empty = mount(ScoreBadge, {
            props: { score: 0, reviewCount: 0 },
        });

        expect(await axe(scored.element, options)).toHaveNoViolations();
        expect(await axe(empty.element, options)).toHaveNoViolations();
    });

    it('DimensionBars are labelled meters with text values', async () => {
        const wrapper = mount(DimensionBars, {
            props: {
                averages: {
                    glass: 4.5,
                    lighting: 3.2,
                    turf: null,
                    facilities: 5,
                },
            },
        });

        expect(wrapper.findAll('[role="meter"]')).toHaveLength(4);
        expect(wrapper.text()).toContain('No data');
        expect(await axe(wrapper.element, options)).toHaveNoViolations();
    });

    it('NativeSelect is a plain labelled control', async () => {
        const wrapper = mount(
            {
                components: { NativeSelect },
                template: `
                    <label for="sort">Sort</label>
                    <NativeSelect id="sort" :options="options" model-value="score" />
                `,
                data: () => ({
                    options: [
                        { value: 'score', label: 'Highest rated' },
                        { value: 'name', label: 'Name' },
                    ],
                }),
            },
            { attachTo: document.body },
        );

        expect(await axe(wrapper.element, options)).toHaveNoViolations();
    });
});
