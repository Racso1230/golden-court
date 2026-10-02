<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Domain\Users\Data\NotificationsSummaryData;
use App\Support\Seo\HeadTagRenderer;
use App\Support\Seo\PageMetaData;
use App\Support\Seo\PrivatePageTitles;
use App\Support\Seo\Site;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Paths rendered on the client only. The public page types are the whole
     * indexable surface; everything behind a login gains nothing from SSR, and
     * keeping it out leaves WebAuthn and two-factor code outside the server
     * renderer's failure domain. A denylist, so a new public route is
     * server-rendered by default.
     *
     * @var array<int, string>
     */
    protected $withoutSsr = [
        'dashboard',
        'settings',
        'settings/*',
        'account/*',
        'admin',
        'admin/*',
        'reviews/*',
        'courts/*/reviews/*',
        'notifications/*',
        'login',
        'register',
        'forgot-password',
        'reset-password/*',
        'email/*',
        'two-factor-challenge',
        'user/*',
        'passkeys/*',
        'up',
    ];

    public function __construct(
        private readonly HeadTagRenderer $head,
        private readonly Site $site,
        private readonly PrivatePageTitles $titles,
    ) {}

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $user = $request->user();

        return [
            ...parent::share($request),
            'name' => config('app.name'),
            'auth' => [
                'user' => $user,
            ],
            'notifications' => fn (): ?NotificationsSummaryData => $user === null ? null : NotificationsSummaryData::forUser($user),
            'sidebarOpen' => ! $request->hasCookie('sidebar_state') || $request->cookie('sidebar_state') === 'true',
            // Head tags for pages whose controller does not build their own; see app/Support/Seo.
            'head' => fn (): array => $this->head->render($this->defaultMeta($request)),
        ];
    }

    /**
     * Private pages are hidden from crawlers; anything else is indexable under
     * its own URL until its controller says more.
     */
    private function defaultMeta(Request $request): PageMetaData
    {
        $route = $request->route();
        $name = $route instanceof Route ? $route->getName() : null;
        $title = $this->titles->for($name);

        if ($title !== null) {
            return PageMetaData::noindex($title);
        }

        if ($route instanceof Route && in_array('auth', $route->gatherMiddleware(), true)) {
            return PageMetaData::noindex($this->site->name)->withoutBrandSuffix();
        }

        return PageMetaData::indexable($this->site->name, $this->site->description, $this->site->url($request->path()))
            ->withoutBrandSuffix();
    }
}
