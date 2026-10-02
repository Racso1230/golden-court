<?php

declare(strict_types=1);

namespace App\Support\Seo;

/**
 * Titles for the pages that only exist behind a login or around it. Being
 * listed here also marks a route as private: the Inertia middleware emits
 * noindex,nofollow and nothing else for it.
 */
final class PrivatePageTitles
{
    /**
     * @var array<string, string>
     */
    private const array TITLES = [
        'dashboard' => 'Dashboard',
        'account.reviews' => 'My reviews',
        'account.claims' => 'My claims',
        'reviews.create' => 'Write a review',
        'reviews.edit' => 'Edit your review',
        'profile.edit' => 'Profile settings',
        'security.edit' => 'Security settings',
        'admin.dashboard' => 'Moderation',
        'admin.claims.index' => 'Pending venue claims',
        'admin.flags.index' => 'Flagged reviews',
        'admin.reviews.pending' => 'Pending reviews',
        'admin.reviews.show' => 'Review under moderation',
        'login' => 'Log in',
        'register' => 'Create an account',
        'password.request' => 'Forgot your password',
        'password.reset' => 'Reset your password',
        'verification.notice' => 'Verify your email address',
        'two-factor.login' => 'Two-factor authentication',
        'password.confirm' => 'Confirm your password',
    ];

    public function for(?string $routeName): ?string
    {
        return $routeName === null ? null : (self::TITLES[$routeName] ?? null);
    }
}
