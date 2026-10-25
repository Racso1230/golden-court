<?php

declare(strict_types=1);

use App\Providers\AppServiceProvider;
use App\Providers\DomainServiceProvider;
use App\Providers\FortifyServiceProvider;
use App\Providers\RateLimitServiceProvider;
use App\Providers\TypeScriptTransformerServiceProvider;
use Spatie\LaravelTypeScriptTransformer\TypeScriptTransformerApplicationServiceProvider;

return [
    AppServiceProvider::class,
    DomainServiceProvider::class,
    FortifyServiceProvider::class,
    RateLimitServiceProvider::class,
    // The type generator is a development tool (spatie/laravel-typescript-transformer
    // is in require-dev), so a production install made with --no-dev does not
    // have it. Registering its provider there would stop the app booting.
    ...(class_exists(TypeScriptTransformerApplicationServiceProvider::class) ? [TypeScriptTransformerServiceProvider::class] : []),
];
