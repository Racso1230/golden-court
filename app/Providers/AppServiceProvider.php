<?php

declare(strict_types=1);

namespace App\Providers;

use App\Support\Health\CheckDatabaseConnection;
use App\Support\Seo\OgImageData;
use App\Support\Seo\Site;
use Carbon\CarbonImmutable;
use Illuminate\Config\Repository;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Events\DiagnosingHealth;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(Site::class, static function (Application $app): Site {
            $config = $app->make(Repository::class);
            $baseUrl = rtrim($config->string('app.url'), '/');
            $imagePath = $config->get('seo.og_image.path');
            $twitterSite = $config->get('seo.twitter_site');

            return new Site(
                name: $config->string('app.name'),
                baseUrl: $baseUrl,
                description: $config->string('seo.description'),
                locale: $config->string('seo.locale'),
                ogLocale: $config->string('seo.og_locale'),
                image: is_string($imagePath) && $imagePath !== '' ? new OgImageData(
                    url: $baseUrl.'/'.ltrim($imagePath, '/'),
                    width: $config->integer('seo.og_image.width'),
                    height: $config->integer('seo.og_image.height'),
                    alt: $config->string('seo.og_image.alt'),
                ) : null,
                twitterSite: is_string($twitterSite) && $twitterSite !== '' ? $twitterSite : null,
            );
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureDefaults();

        Event::listen(DiagnosingHealth::class, CheckDatabaseConnection::class);
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        Model::shouldBeStrict();

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null,
        );
    }
}
