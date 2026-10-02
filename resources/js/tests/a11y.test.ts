import { mount } from '@vue/test-utils';
import { describe, expect, it } from 'vitest';
import { axe } from 'vitest-axe';
import DimensionBars from '@/components/DimensionBars.vue';
import NativeSelect from '@/components/NativeSelect.vue';
import RatingInput from '@/components/RatingInput.vue';
import RatingStars from '@/components/RatingStars.vue';
import ScoreBadge from '@/components/ScoreBadge.vue';
import EmptyState from '@/components/EmptyState.vue';
import PageHeader from '@/components/PageHeader.vue';
import SectionCard from '@/components/SectionCard.vue';
import StatCard from '@/components/StatCard.vue';
import StatusBadge from '@/components/StatusBadge.vue';
import { Textarea } from '@/components/ui/textarea';

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

describe('accessibility of the page primitives', () => {
    it('Textarea is a labelled control', async () => {
        const wrapper = mount(
            {
                components: { Textarea },
                template: `
                    <label for="body">Your review</label>
                    <Textarea id="body" model-value="" />
                `,
            },
            { attachTo: document.body },
        );

        expect(wrapper.find('textarea#body').exists()).toBe(true);
        expect(await axe(wrapper.element, options)).toHaveNoViolations();
    });

    it('PageHeader renders one h1 with its eyebrow and description', async () => {
        const wrapper = mount(PageHeader, {
            props: {
                title: 'Settings',
                eyebrow: 'Your account',
                description: 'Manage your profile.',
            },
        });

        expect(wrapper.findAll('h1')).toHaveLength(1);
        expect(wrapper.find('h1').text()).toBe('Settings');
        expect(wrapper.text()).toContain('Your account');
        expect(await axe(wrapper.element, options)).toHaveNoViolations();
    });

    it('EmptyState hides its icon from assistive technology', async () => {
        const wrapper = mount(EmptyState, {
            props: { title: 'Nothing yet', description: 'Check back soon.' },
            slots: { icon: '<svg></svg>' },
        });

        expect(wrapper.find('[aria-hidden="true"] svg').exists()).toBe(true);
        expect(await axe(wrapper.element, options)).toHaveNoViolations();
    });

    it('SectionCard titles its section with an h2', async () => {
        const wrapper = mount(SectionCard, {
            props: { title: 'Profile', description: 'Your details' },
            slots: { default: '<p>Body</p>' },
        });

        expect(wrapper.find('section h2').text()).toBe('Profile');
        expect(await axe(wrapper.element, options)).toHaveNoViolations();
    });

    it('StatCard shows its label, value and hint as text', async () => {
        const wrapper = mount(StatCard, {
            props: { label: 'Reviews written', value: 4, hint: '1 pending' },
        });

        expect(wrapper.text()).toContain('Reviews written');
        expect(wrapper.text()).toContain('4');
        expect(await axe(wrapper.element, options)).toHaveNoViolations();
    });

    it('StatusBadge carries its label as text, not colour alone', async () => {
        const wrapper = mount(StatusBadge, {
            props: { tone: 'warning' },
            slots: { default: 'Pending' },
        });

        expect(wrapper.text()).toBe('Pending');
        expect(await axe(wrapper.element, options)).toHaveNoViolations();
    });
});
