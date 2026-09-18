<?php

declare(strict_types=1);

namespace App\Providers;

use Carbon\CarbonImmutable;
use Spatie\LaravelTypeScriptTransformer\TypeScriptTransformerApplicationServiceProvider as BaseTypeScriptTransformerServiceProvider;
use Spatie\TypeScriptTransformer\Transformers\AttributedClassTransformer;
use Spatie\TypeScriptTransformer\Transformers\EnumTransformer;
use Spatie\TypeScriptTransformer\TypeScriptTransformerConfigFactory;
use Spatie\TypeScriptTransformer\Writers\GlobalNamespaceWriter;

/**
 * Generates resources/js/types/generated.d.ts from the domain's Data classes,
 * value objects marked #[TypeScript] and every backed enum. The frontend
 * never hand-writes a type that mirrors one of these.
 */
class TypeScriptTransformerServiceProvider extends BaseTypeScriptTransformerServiceProvider
{
    protected function configure(TypeScriptTransformerConfigFactory $config): void
    {
        $config
            ->transformer(AttributedClassTransformer::class)
            ->transformer(EnumTransformer::class)
            ->transformDirectories(app_path('Domain'))
            // Dates cross the wire as ISO-8601 strings.
            ->replaceType(CarbonImmutable::class, 'string')
            ->outputDirectory(resource_path('js/types'))
            ->withoutManifest()
            ->writer(new GlobalNamespaceWriter('generated.d.ts'));
    }
}
