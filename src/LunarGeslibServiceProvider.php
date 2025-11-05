<?php

declare(strict_types=1);

namespace NumaxLab\Lunar\Geslib;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Lunar\Admin\Filament\Resources\CollectionResource;
use Lunar\Admin\Filament\Resources\ProductResource;
use Lunar\Admin\Filament\Resources\ProductResource\Pages\ManageProductCollections;
use Lunar\Admin\Support\Facades\AttributeData;
use Lunar\Admin\Support\Facades\LunarPanel;
use Lunar\Facades\AttributeManifest;
use Lunar\Facades\ModelManifest;
use NumaxLab\Lunar\Geslib\Admin\Filament\Extension\CollectionResourceExtension;
use NumaxLab\Lunar\Geslib\Admin\Filament\Extension\ManageProductCollectionsExtension;
use NumaxLab\Lunar\Geslib\Admin\Filament\Extension\ProductResourceExtension;
use NumaxLab\Lunar\Geslib\Admin\Support\FieldTypes\DateField;
use NumaxLab\Lunar\Geslib\Console\Commands\Geslib\ForceProductEnrichment;
use NumaxLab\Lunar\Geslib\Console\Commands\Geslib\Import;
use NumaxLab\Lunar\Geslib\Console\Commands\Geslib\ImportBatchLines;
use NumaxLab\Lunar\Geslib\Console\Commands\ImportAddressData;
use NumaxLab\Lunar\Geslib\Console\Commands\Install;
use NumaxLab\Lunar\Geslib\Console\Commands\Search\EnsureIndexes;
use NumaxLab\Lunar\Geslib\FieldTypes\Date;
use NumaxLab\Lunar\Geslib\Listeners\EnrichProductFromDilveSubscriber;
use NumaxLab\Lunar\Geslib\Models\Author;
use Spatie\ArrayToXml\ArrayToXml;
use Spatie\StructureDiscoverer\Discover;

class LunarGeslibServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/geslib.php', 'lunar.geslib');

        ModelManifest::add(
            \NumaxLab\Lunar\Geslib\Models\Contracts\Author::class,
            Author::class,
        );

        ModelManifest::replace(
            \Lunar\Models\Contracts\Product::class,
            \NumaxLab\Lunar\Geslib\Models\Product::class,
        );

        ModelManifest::replace(
            \Lunar\Models\Contracts\ProductVariant::class,
            \NumaxLab\Lunar\Geslib\Models\ProductVariant::class,
        );

        AttributeData::registerFieldType(Date::class, DateField::class);

        AttributeManifest::addType(Author::class);

        LunarPanel::extensions([
            CollectionResource::class => CollectionResourceExtension::class,
            ManageProductCollections::class => ManageProductCollectionsExtension::class,
            ProductResource::class => ProductResourceExtension::class,
        ]);
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'lunar-geslib');
        $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'lunar-geslib');

        if (config('lunar.geslib.api_routes_enabled', false)) {
            $this->loadRoutesFrom(__DIR__.'/../routes/api.php');
        }

        $this->publishes([
            __DIR__.'/../config/geslib.php' => config_path('lunar/geslib.php'),
            __DIR__.'/../resources/views' => resource_path('views/vendor/lunar/geslib'),
            __DIR__.'/../routes/storefront.php' => base_path('routes/storefront.php'),
        ], ['lunar']);

        Event::subscribe(EnrichProductFromDilveSubscriber::class);

        $modelClasses = collect(
            Discover::in(__DIR__.'/Models')
                ->classes()
                ->extending(Model::class)
                ->get(),
        )->mapWithKeys(
            fn ($class) => [
                Str::snake(str_replace('\\', '_', Str::after($class, 'NumaxLab\\Lunar\\Geslib\\Models\\'))) => $class,
            ],
        );

        Relation::morphMap($modelClasses->toArray());

        Response::macro(
            'xml',
            function (array $data, int $status = 200, array $headers = [], string $rootElement = 'response') {
                $xml = ArrayToXml::convert($data, $rootElement, true, 'utf-8');

                return Response::make($xml, $status, array_merge($headers, [
                    'Content-Type' => 'application/xml',
                ]));
            },
        );

        if ($this->app->runningInConsole()) {
            $commands = [
                Install::class,
                Import::class,
                ImportBatchLines::class,
                ImportAddressData::class,
                ForceProductEnrichment::class,
            ];

            if (! $this->app->runningUnitTests()) {
                $commands[] = EnsureIndexes::class;
            }

            $this->commands($commands);
        }
    }
}
