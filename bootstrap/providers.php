<?php

declare(strict_types=1);

use App\Providers\AppServiceProvider;
use App\Providers\DomainServiceProvider;
use App\Providers\FortifyServiceProvider;
use App\Providers\RateLimitServiceProvider;
use App\Providers\TypeScriptTransformerServiceProvider;

return [
    AppServiceProvider::class,
    DomainServiceProvider::class,
    FortifyServiceProvider::class,
    RateLimitServiceProvider::class,
    TypeScriptTransformerServiceProvider::class,
];
