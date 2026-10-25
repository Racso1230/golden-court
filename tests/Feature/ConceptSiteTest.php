<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Laravel\Fortify\Features;
use Tests\TestCase;

/**
 * The public portfolio deployment runs Golden Court as a concept: sign-up is
 * switched off and every page carries a notice that the venues and reviews are
 * fictional. Both are shared props, so these tests read them from the home page.
 */
class ConceptSiteTest extends TestCase
{
    use RefreshDatabase;

    public function test_by_default_sign_up_is_offered_and_there_is_no_concept_banner(): void
    {
        $this->get(route('home'))->assertInertia(
            fn (Assert $page) => $page
                ->where('canRegister', true)
                ->where('conceptBanner', false),
        );
    }

    public function test_the_concept_banner_can_be_switched_on(): void
    {
        config(['golden_court.site.concept_banner' => true]);

        $this->get(route('home'))->assertInertia(
            fn (Assert $page) => $page->where('conceptBanner', true),
        );
    }

    public function test_pages_stop_offering_sign_up_when_registration_is_switched_off(): void
    {
        config(['fortify.features' => [Features::resetPasswords()]]);

        $this->get(route('home'))->assertInertia(
            fn (Assert $page) => $page->where('canRegister', false),
        );
    }

    public function test_the_login_page_also_carries_the_flags(): void
    {
        config([
            'golden_court.site.concept_banner' => true,
            'fortify.features' => [Features::resetPasswords()],
        ]);

        $this->get(route('login'))->assertInertia(
            fn (Assert $page) => $page
                ->component('auth/Login')
                ->where('canRegister', false)
                ->where('conceptBanner', true),
        );
    }
}
