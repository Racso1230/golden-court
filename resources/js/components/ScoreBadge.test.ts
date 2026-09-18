import { mount } from '@vue/test-utils';
import { describe, expect, it } from 'vitest';
import ScoreBadge from '@/components/ScoreBadge.vue';

describe('ScoreBadge', () => {
    it('shows the score and pluralised review count', () => {
        const wrapper = mount(ScoreBadge, {
            props: { score: 4.3, reviewCount: 12 },
        });

        expect(wrapper.text()).toContain('4.3');
        expect(wrapper.text()).toContain('12 reviews');
    });

    it('uses the singular for one review', () => {
        expect(
            mount(ScoreBadge, { props: { score: 5, reviewCount: 1 } }).text(),
        ).toContain('1 review');
    });

    it('says so instead of showing a score when there are no reviews', () => {
        const wrapper = mount(ScoreBadge, {
            props: { score: 0, reviewCount: 0 },
        });

        expect(wrapper.text()).toBe('No reviews yet');
        expect(wrapper.find('[role="img"]').exists()).toBe(false);
    });
});
