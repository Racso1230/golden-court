import { mount } from '@vue/test-utils';
import { describe, expect, it } from 'vitest';
import ConceptBanner from '@/components/ConceptBanner.vue';

describe('ConceptBanner', () => {
    it('says the site is a concept with fictional venues and reviews', () => {
        const wrapper = mount(ConceptBanner);

        expect(wrapper.text()).toContain('This is a concept.');
        expect(wrapper.text()).toContain('venues and reviews are fictional');
    });

    it('links to the studio that made it', () => {
        const link = mount(ConceptBanner).find('a');

        expect(link.attributes('href')).toBe('https://ojfdigital.co.uk');
        expect(link.text()).toBe('OJF Digital');
    });

    it('is a labelled note, not an alert', () => {
        const banner = mount(ConceptBanner).find('[role="note"]');

        expect(banner.exists()).toBe(true);
        expect(banner.attributes('aria-label')).toBe('About this site');
    });
});
