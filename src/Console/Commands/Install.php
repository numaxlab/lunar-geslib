<?php

namespace NumaxLab\Lunar\Geslib\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Lunar\Admin\Models\Staff;
use Lunar\FieldTypes\Dropdown;
use Lunar\FieldTypes\Number;
use Lunar\FieldTypes\Text;
use Lunar\Models\Attribute;
use Lunar\Models\AttributeGroup;
use Lunar\Models\Brand;
use Lunar\Models\Channel;
use Lunar\Models\Collection;
use Lunar\Models\CollectionGroup;
use Lunar\Models\Country;
use Lunar\Models\Currency;
use Lunar\Models\CustomerGroup;
use Lunar\Models\Language;
use Lunar\Models\Product;
use Lunar\Models\ProductType;
use Lunar\Models\TaxClass;
use Lunar\Models\TaxZone;
use NumaxLab\Lunar\Geslib\FieldTypes\Date;
use NumaxLab\Lunar\Geslib\Geslib\AuthorCommand;
use NumaxLab\Lunar\Geslib\Geslib\BindingTypeCommand;
use NumaxLab\Lunar\Geslib\Geslib\ClassificationCommand;
use NumaxLab\Lunar\Geslib\Geslib\EditorialCommand;
use NumaxLab\Lunar\Geslib\Geslib\LanguageCommand;
use NumaxLab\Lunar\Geslib\Geslib\TypeCommand;

use function Laravel\Prompts\confirm;

class Install extends Command
{
    protected $signature = 'lunar:geslib:install';

    protected $description = 'Install Lunar Geslib data and configuration';

    public function handle(): void
    {
        $this->components->info('Installing...');

        $this->components->info('Publishing configuration...');

        if (!$this->configExists('lunar')) {
            $this->publishConfiguration();
        } else {
            if ($this->shouldOverwriteConfig()) {
                $this->components->info('Overwriting configuration file...');
                $this->publishConfiguration(forcePublish: true);
            } else {
                $this->components->info('Existing configuration was not overwritten');
            }
        }

        if (confirm('Run database migrations?')) {
            $this->call('migrate');
        }

        DB::beginTransaction();

        if (class_exists(Staff::class) && !Staff::whereAdmin(true)->exists()) {
            $this->components->info('First create a lunar admin user');
            $this->call('lunar:create-admin');
        }

        if (!Country::count()) {
            $this->components->info('Importing countries');
            $this->call('lunar:import:address-data');
        }

        if (!Channel::whereDefault(true)->exists()) {
            $this->components->info('Setting up default channel');

            Channel::create([
                'name' => 'Tienda online',
                'handle' => 'webstore',
                'default' => true,
                'url' => config('app.url'),
            ]);
        }

        if (!Language::count()) {
            $this->components->info('Adding default language');

            Language::create([
                'code' => 'es',
                'name' => 'Español',
                'default' => true,
            ]);
        }

        if (!Currency::whereDefault(true)->exists()) {
            $this->components->info('Adding currency (EUR)');

            Currency::create([
                'code' => 'EUR',
                'name' => 'Euro',
                'exchange_rate' => 1,
                'decimal_places' => 2,
                'default' => true,
                'enabled' => true,
            ]);
        }

        if (!CustomerGroup::whereDefault(true)->exists()) {
            $this->components->info('Adding a default customer group.');

            CustomerGroup::create([
                'name' => 'Venta al por menor',
                'handle' => 'retail',
                'default' => true,
            ]);
        }

        if (!TaxClass::count()) {
            $this->components->info('Adding tax classes.');

            TaxClass::create([
                'name' => 'IVA',
                'default' => true,
            ]);
        }

        if (!TaxZone::count()) {
            $this->components->info('Adding tax zones.');

            $taxZone = TaxZone::create([
                'name' => 'España peninsular + Baleares',
                'zone_type' => 'country',
                'price_display' => 'tax_exclusive',
                'default' => true,
                'active' => true,
            ]);
            $taxZone->countries()->createMany(
                Country::get()->map(fn($country)
                    => [
                    'country_id' => $country->id,
                ]),
            );
        }

        if (!CollectionGroup::count()) {
            $this->components->info('Adding an collection groups.');

            $this->setupCollectionGroups();
        }

        if (!Attribute::count()) {
            $this->components->info('Setting up attributes.');

            $this->setupBrandAttributes();
            $this->setupCollectionAttributes();
            $this->setupArticleAttributes();
        }

        if (!ProductType::count()) {
            $this->components->info('Adding product types.');

            $type = ProductType::create([
                'name' => 'Artículo Geslib',
            ]);

            $type->mappedAttributes()->attach(
                Attribute::whereAttributeType(
                    Product::morphName(),
                )->get()->pluck('id'),
            );
        }

        DB::commit();

        $this->components->info('Publishing Filament assets');
        $this->call('filament:assets');
    }

    private function configExists(string $fileName): bool
    {
        if (!File::isDirectory(config_path($fileName))) {
            return false;
        }

        return !empty(File::allFiles(config_path($fileName)));
    }

    /**
     * Publishes configuration for the Service Provider.
     */
    private function publishConfiguration(bool $forcePublish = false): void
    {
        $params = [
            '--tag' => 'lunar',
        ];

        if ($forcePublish === true) {
            $params['--force'] = true;
        }

        $this->call('vendor:publish', $params);
    }

    /**
     * Returns a prompt if config exists and ask to override it.
     */
    private function shouldOverwriteConfig(): bool
    {
        return confirm(
            'Config file already exists. Do you want to overwrite it?',
            false,
        );
    }

    private function setupCollectionGroups(): void
    {
        CollectionGroup::create([
            'name' => 'Tipos de artículos',
            'handle' => TypeCommand::HANDLE,
        ]);

        CollectionGroup::create([
            'name' => 'Idiomas',
            'handle' => LanguageCommand::HANDLE,
        ]);

        CollectionGroup::create([
            'name' => 'Materias',
            'handle' => 'categories',
        ]);

        CollectionGroup::create([
            'name' => 'Materias IBIC',
            'handle' => 'ibic',
        ]);

        CollectionGroup::create([
            'name' => 'Colecciones editoriales',
            'handle' => 'editorial-collections',
        ]);

        CollectionGroup::create([
            'name' => 'Autores',
            'handle' => AuthorCommand::HANDLE,
        ]);

        CollectionGroup::create([
            'name' => 'Formatos de encuadernación',
            'handle' => BindingTypeCommand::HANDLE,
        ]);

        CollectionGroup::create([
            'name' => 'Estados',
            'handle' => 'statuses',
        ]);

        CollectionGroup::create([
            'name' => 'Clasificaciones',
            'handle' => ClassificationCommand::HANDLE,
        ]);
    }

    private function setupBrandAttributes(): void
    {
        $group = AttributeGroup::create([
            'attributable_type' => Brand::morphName(),
            'name' => collect([
                'es' => 'Datos de Geslib',
            ]),
            'handle' => 'geslib-provider',
            'position' => 1,
        ]);

        Attribute::create([
            'attribute_type' => Brand::morphName(),
            'attribute_group_id' => $group->id,
            'position' => 1,
            'handle' => 'type',
            'name' => [
                'es' => 'Tipo',
            ],
            'description' => [
                'es' => '',
            ],
            'section' => 'main',
            'type' => Dropdown::class,
            'required' => true,
            'default_value' => null,
            'configuration' => [
                'lookups' => [
                    [
                        'label' => 'Editorial',
                        'value' => EditorialCommand::BRAND_TYPE,
                    ],
                    [
                        'label' => 'Discográfica',
                        'value' => 'record-company',
                    ],
                    [
                        'label' => 'Familia de papelería',
                        'value' => 'stationery-category',
                    ],
                    [
                        'label' => 'Publicaciones de prensa',
                        'value' => 'press-releases',
                    ],
                ],
            ],
            'system' => true,
            'searchable' => true,
        ]);

        Attribute::create([
            'attribute_type' => Brand::morphName(),
            'attribute_group_id' => $group->id,
            'position' => 2,
            'handle' => 'geslib-code',
            'name' => [
                'es' => 'Código Geslib',
            ],
            'description' => [
                'es' => '',
            ],
            'section' => 'main',
            'type' => Number::class,
            'required' => true,
            'default_value' => null,
            'configuration' => [
                'min' => null,
                'max' => null,
            ],
            'system' => true,
            'searchable' => true,
        ]);

        Attribute::create([
            'attribute_type' => Brand::morphName(),
            'attribute_group_id' => $group->id,
            'position' => 3,
            'handle' => 'external-name',
            'name' => [
                'es' => 'Nombre externo',
            ],
            'description' => [
                'es' => '',
            ],
            'section' => 'main',
            'type' => Text::class,
            'required' => true,
            'default_value' => null,
            'configuration' => [
                'richtext' => false,
            ],
            'system' => false,
            'searchable' => false,
        ]);

        Attribute::create([
            'attribute_type' => Brand::morphName(),
            'attribute_group_id' => $group->id,
            'position' => 2,
            'handle' => 'country',
            'name' => [
                'es' => 'País',
            ],
            'description' => [
                'es' => '',
            ],
            'section' => 'main',
            'type' => Text::class,
            'required' => true,
            'default_value' => null,
            'configuration' => [
                'richtext' => false,
            ],
            'system' => false,
            'searchable' => false,
        ]);
    }

    private function setupCollectionAttributes(): void
    {
        $group = AttributeGroup::create([
            'attributable_type' => Collection::morphName(),
            'name' => collect([
                'es' => 'Datos básicos',
            ]),
            'handle' => 'main',
            'position' => 1,
        ]);

        Attribute::create([
            'attribute_type' => Collection::morphName(),
            'attribute_group_id' => $group->id,
            'position' => 2,
            'handle' => 'geslib-code',
            'name' => [
                'es' => 'Código Geslib',
            ],
            'description' => [
                'es' => '',
            ],
            'section' => 'main',
            'type' => Text::class,
            'required' => true,
            'default_value' => null,
            'configuration' => [
                'min' => null,
                'max' => null,
            ],
            'system' => true,
            'searchable' => true,
        ]);

        Attribute::create([
            'attribute_type' => Collection::morphName(),
            'attribute_group_id' => $group->id,
            'position' => 2,
            'handle' => 'name',
            'name' => [
                'es' => 'Nombre',
            ],
            'description' => [
                'es' => '',
            ],
            'section' => 'main',
            'type' => Text::class,
            'required' => true,
            'default_value' => null,
            'configuration' => [
                'richtext' => false,
            ],
            'system' => false,
            'searchable' => true,
        ]);
    }

    private function setupArticleAttributes(): void
    {
        $mainGroup = AttributeGroup::create([
            'attributable_type' => Product::morphName(),
            'name' => collect([
                'es' => 'Artículo',
            ]),
            'handle' => 'book-main',
            'position' => 1,
        ]);

        Attribute::create([
            'attribute_type' => Product::morphName(),
            'attribute_group_id' => $mainGroup->id,
            'position' => 1,
            'handle' => 'name',
            'name' => [
                'es' => 'Título',
            ],
            'description' => [
                'es' => '',
            ],
            'section' => 'main',
            'type' => Text::class,
            'required' => true,
            'default_value' => null,
            'configuration' => [
                'richtext' => false,
            ],
            'system' => false,
            'searchable' => true,
        ]);

        Attribute::create([
            'attribute_type' => Product::morphName(),
            'attribute_group_id' => $mainGroup->id,
            'position' => 2,
            'handle' => 'subtitle',
            'name' => [
                'es' => 'Subtítulo',
            ],
            'description' => [
                'es' => '',
            ],
            'section' => 'main',
            'type' => Text::class,
            'required' => false,
            'default_value' => null,
            'configuration' => [
                'richtext' => false,
            ],
            'system' => false,
            'searchable' => true,
        ]);

        Attribute::create([
            'attribute_type' => Product::morphName(),
            'attribute_group_id' => $mainGroup->id,
            'position' => 3,
            'handle' => 'created-at',
            'name' => [
                'es' => 'Fecha de alta',
            ],
            'description' => [
                'es' => '',
            ],
            'section' => 'main',
            'type' => Date::class,
            'required' => false,
            'default_value' => null,
            'configuration' => [
                'has_time' => false,
            ],
            'system' => false,
            'searchable' => false,
        ]);

        Attribute::create([
            'attribute_type' => Product::morphName(),
            'attribute_group_id' => $mainGroup->id,
            'position' => 4,
            'handle' => 'novelty-date',
            'name' => [
                'es' => 'Fecha de novedad',
            ],
            'description' => [
                'es' => '',
            ],
            'section' => 'main',
            'type' => Date::class,
            'required' => false,
            'default_value' => null,
            'configuration' => [
                'has_time' => false,
            ],
            'system' => false,
            'searchable' => false,
        ]);

        $bibliographicDataGroup = AttributeGroup::create([
            'attributable_type' => Product::morphName(),
            'name' => collect([
                'es' => 'Datos bibliográficos',
            ]),
            'handle' => 'bibliographic-data',
            'position' => 2,
        ]);

        Attribute::create([
            'attribute_type' => Product::morphName(),
            'attribute_group_id' => $bibliographicDataGroup->id,
            'position' => 1,
            'handle' => 'issue-date',
            'name' => [
                'es' => 'Fecha de edición',
            ],
            'description' => [
                'es' => '',
            ],
            'section' => 'main',
            'type' => Date::class,
            'required' => false,
            'default_value' => null,
            'configuration' => [
                'has_time' => false,
            ],
            'system' => false,
            'searchable' => false,
        ]);

        Attribute::create([
            'attribute_type' => Product::morphName(),
            'attribute_group_id' => $bibliographicDataGroup->id,
            'position' => 1,
            'handle' => 'first-issue-year',
            'name' => [
                'es' => 'Año de primera edición',
            ],
            'description' => [
                'es' => '',
            ],
            'section' => 'main',
            'type' => Number::class,
            'required' => false,
            'default_value' => null,
            'configuration' => [
                'min' => null,
                'max' => null,
            ],
            'system' => false,
            'searchable' => false,
        ]);

        Attribute::create([
            'attribute_type' => Product::morphName(),
            'attribute_group_id' => $bibliographicDataGroup->id,
            'position' => 3,
            'handle' => 'edition-number',
            'name' => [
                'es' => 'Número de edición',
            ],
            'description' => [
                'es' => '',
            ],
            'section' => 'main',
            'type' => Text::class,
            'required' => false,
            'default_value' => null,
            'configuration' => [
                'richtext' => false,
            ],
            'system' => false,
            'searchable' => false,
        ]);

        Attribute::create([
            'attribute_type' => Product::morphName(),
            'attribute_group_id' => $bibliographicDataGroup->id,
            'position' => 4,
            'handle' => 'reissue-date',
            'name' => [
                'es' => 'Fecha de reedición',
            ],
            'description' => [
                'es' => '',
            ],
            'section' => 'main',
            'type' => Date::class,
            'required' => false,
            'default_value' => null,
            'configuration' => [
                'has_time' => false,
            ],
            'system' => false,
            'searchable' => false,
        ]);

        Attribute::create([
            'attribute_type' => Product::morphName(),
            'attribute_group_id' => $bibliographicDataGroup->id,
            'position' => 5,
            'handle' => 'last-issue-year',
            'name' => [
                'es' => 'Año de última edición',
            ],
            'description' => [
                'es' => '',
            ],
            'section' => 'main',
            'type' => Number::class,
            'required' => false,
            'default_value' => null,
            'configuration' => [
                'min' => null,
                'max' => null,
            ],
            'system' => false,
            'searchable' => false,
        ]);

        Attribute::create([
            'attribute_type' => Product::morphName(),
            'attribute_group_id' => $bibliographicDataGroup->id,
            'position' => 6,
            'handle' => 'edition-origin',
            'name' => [
                'es' => 'Origen de edición',
            ],
            'description' => [
                'es' => '',
            ],
            'section' => 'main',
            'type' => Text::class,
            'required' => false,
            'default_value' => null,
            'configuration' => [
                'richtext' => false,
            ],
            'system' => false,
            'searchable' => false,
        ]);

        Attribute::create([
            'attribute_type' => Product::morphName(),
            'attribute_group_id' => $bibliographicDataGroup->id,
            'position' => 7,
            'handle' => 'pages',
            'name' => [
                'es' => 'Páginas',
            ],
            'description' => [
                'es' => '',
            ],
            'section' => 'main',
            'type' => Number::class,
            'required' => false,
            'default_value' => null,
            'configuration' => [
                'min' => null,
                'max' => null,
            ],
            'system' => true,
            'searchable' => false,
        ]);

        Attribute::create([
            'attribute_type' => Product::morphName(),
            'attribute_group_id' => $bibliographicDataGroup->id,
            'position' => 8,
            'handle' => 'illustrations-quantity',
            'name' => [
                'es' => 'Número de ilustraciones',
            ],
            'description' => [
                'es' => '',
            ],
            'section' => 'main',
            'type' => Number::class,
            'required' => false,
            'default_value' => null,
            'configuration' => [
                'min' => null,
                'max' => null,
            ],
            'system' => true,
            'searchable' => false,
        ]);

        $referencesDataGroup = AttributeGroup::create([
            'attributable_type' => Product::morphName(),
            'name' => collect([
                'es' => 'Referencias',
            ]),
            'handle' => 'references-data',
            'position' => 3,
        ]);

        Attribute::create([
            'attribute_type' => Product::morphName(),
            'attribute_group_id' => $referencesDataGroup->id,
            'position' => 1,
            'handle' => 'editorial-reference',
            'name' => [
                'es' => 'Referencias del editor',
            ],
            'description' => [
                'es' => '',
            ],
            'section' => 'main',
            'type' => Text::class,
            'required' => false,
            'default_value' => null,
            'configuration' => [
                'richtext' => true,
            ],
            'system' => false,
            'searchable' => false,
        ]);

        Attribute::create([
            'attribute_type' => Product::morphName(),
            'attribute_group_id' => $referencesDataGroup->id,
            'position' => 2,
            'handle' => 'bookshop-reference',
            'name' => [
                'es' => 'Referencias de la librería',
            ],
            'description' => [
                'es' => '',
            ],
            'section' => 'main',
            'type' => Text::class,
            'required' => false,
            'default_value' => null,
            'configuration' => [
                'richtext' => true,
            ],
            'system' => false,
            'searchable' => false,
        ]);

        Attribute::create([
            'attribute_type' => Product::morphName(),
            'attribute_group_id' => $referencesDataGroup->id,
            'position' => 3,
            'handle' => 'index',
            'name' => [
                'es' => 'Índice',
            ],
            'description' => [
                'es' => '',
            ],
            'section' => 'main',
            'type' => Text::class,
            'required' => false,
            'default_value' => null,
            'configuration' => [
                'richtext' => true,
            ],
            'system' => false,
            'searchable' => false,
        ]);
    }
}
