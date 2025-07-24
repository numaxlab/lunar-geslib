<?php

namespace NumaxLab\Lunar\Geslib\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Lunar\Admin\Models\Staff;
use Lunar\FieldTypes\Dropdown;
use Lunar\FieldTypes\Number;
use Lunar\FieldTypes\Text;
use Lunar\FieldTypes\Toggle;
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
use Lunar\Models\State;
use Lunar\Models\TaxClass;
use Lunar\Models\TaxRate;
use Lunar\Models\TaxRateAmount;
use Lunar\Models\TaxZone;
use NumaxLab\Lunar\Geslib\FieldTypes\Date;
use NumaxLab\Lunar\Geslib\Handle;
use NumaxLab\Lunar\Geslib\InterCommands\AuthorCommand;
use NumaxLab\Lunar\Geslib\InterCommands\BindingTypeCommand;
use NumaxLab\Lunar\Geslib\InterCommands\ClassificationCommand;
use NumaxLab\Lunar\Geslib\InterCommands\CollectionCommand;
use NumaxLab\Lunar\Geslib\InterCommands\EditorialCommand;
use NumaxLab\Lunar\Geslib\InterCommands\IbicCommand;
use NumaxLab\Lunar\Geslib\InterCommands\LanguageCommand;
use NumaxLab\Lunar\Geslib\InterCommands\StatusCommand;
use NumaxLab\Lunar\Geslib\InterCommands\TypeCommand;

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
            $this->call('lunar:geslib:import:address-data');
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

        $this->setupTaxation();

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

    /**
     * @return void
     */
    protected function setupTaxation(): void
    {
        if (TaxClass::count() > 0 || TaxZone::count() > 0 || TaxRate::count() > 0) {
            return;
        }

        $this->components->info('Adding tax classes.');

        $generalVatTaxClass = TaxClass::create([
            'name' => 'IVA general',
            'default' => true,
        ]);

        $reducedVatTaxClass = TaxClass::create([
            'name' => 'IVA reducido',
            'default' => false,
        ]);

        $superReducedVatTaxClass = TaxClass::create([
            'name' => 'IVA superreducido',
            'default' => false,
        ]);

        $this->components->info('Adding tax zones.');

        $taxZone = TaxZone::create([
            'name' => 'España peninsular + Baleares',
            'zone_type' => 'states',
            'price_display' => 'tax_inclusive',
            'default' => true,
            'active' => true,
        ]);

        $spain = Country::where('iso2', 'ES')->first();

        $taxZone->countries()->create([
            'country_id' => $spain->id,
        ]);
        $taxZone->states()->createMany(
            State::where('country_id', $spain->id)
                ->whereNotIn('code', ['CE', 'ML', 'CN'])
                ->get()
                ->map(fn($state)
                    => [
                    'state_id' => $state->id,
                ]),
        );

        $this->components->info('Adding tax rates.');

        $taxRate = TaxRate::create([
            'name' => 'IVA',
            'tax_zone_id' => $taxZone->id,
        ]);

        TaxRateAmount::create([
            'tax_rate_id' => $taxRate->id,
            'tax_class_id' => $generalVatTaxClass->id,
            'percentage' => 21,
        ]);

        TaxRateAmount::create([
            'tax_rate_id' => $taxRate->id,
            'tax_class_id' => $reducedVatTaxClass->id,
            'percentage' => 10,
        ]);

        TaxRateAmount::create([
            'tax_rate_id' => $taxRate->id,
            'tax_class_id' => $superReducedVatTaxClass->id,
            'percentage' => 4,
        ]);
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
            'name' => 'Materias IBIC',
            'handle' => IbicCommand::HANDLE,
        ]);

        CollectionGroup::create([
            'name' => 'Colecciones editoriales',
            'handle' => CollectionCommand::HANDLE,
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
            'handle' => StatusCommand::HANDLE,
        ]);

        CollectionGroup::create([
            'name' => 'Clasificaciones',
            'handle' => ClassificationCommand::HANDLE,
        ]);

        CollectionGroup::create([
            'name' => 'Taxonomías',
            'handle' => Handle::COLLECTION_GROUP_TAXONOMIES,
        ]);

        CollectionGroup::create([
            'name' => 'Destacados',
            'handle' => Handle::COLLECTION_GROUP_FEATURED,
        ]);

        CollectionGroup::create([
            'name' => 'Itinerarios',
            'handle' => Handle::COLLECTION_GROUP_ITINERARIES,
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
            'position' => 3,
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
            'handle' => 'collection-main',
            'position' => 1,
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

        Attribute::create([
            'attribute_type' => Collection::morphName(),
            'attribute_group_id' => $group->id,
            'position' => 3,
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
            'searchable' => false,
        ]);

        Attribute::create([
            'attribute_type' => Collection::morphName(),
            'attribute_group_id' => $group->id,
            'position' => 4,
            'handle' => 'description',
            'name' => [
                'es' => 'Descripción',
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
            'attribute_type' => Collection::morphName(),
            'attribute_group_id' => $group->id,
            'position' => 5,
            'handle' => 'is-section',
            'name' => [
                'es' => 'Es sección',
            ],
            'description' => [
                'es' => '',
            ],
            'section' => 'main',
            'type' => Toggle::class,
            'required' => false,
            'default_value' => null,
            'configuration' => [
                'richtext' => false,
            ],
            'system' => false,
            'searchable' => false,
        ]);

        Attribute::create([
            'attribute_type' => Collection::morphName(),
            'attribute_group_id' => $group->id,
            'position' => 6,
            'handle' => 'in-homepage',
            'name' => [
                'es' => 'Mostrar en portada',
            ],
            'description' => [
                'es' => '',
            ],
            'section' => 'main',
            'type' => Toggle::class,
            'required' => false,
            'default_value' => null,
            'configuration' => [
                'richtext' => false,
            ],
            'system' => false,
            'searchable' => false,
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
            'handle' => 'original-title',
            'name' => [
                'es' => 'Título original',
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
            'attribute_group_id' => $bibliographicDataGroup->id,
            'position' => 8,
            'handle' => 'original-language',
            'name' => [
                'es' => 'Idioma original',
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
            'position' => 9,
            'handle' => 'translator',
            'name' => [
                'es' => 'Traducción',
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
            'attribute_group_id' => $bibliographicDataGroup->id,
            'position' => 10,
            'handle' => 'illustrator',
            'name' => [
                'es' => 'Ilustración',
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
            'attribute_group_id' => $bibliographicDataGroup->id,
            'position' => 11,
            'handle' => 'cover-illustrator',
            'name' => [
                'es' => 'Ilustración de portada',
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
            'attribute_group_id' => $bibliographicDataGroup->id,
            'position' => 12,
            'handle' => 'back-cover-illustrator',
            'name' => [
                'es' => 'Ilustración de contraportada',
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
            'attribute_group_id' => $bibliographicDataGroup->id,
            'position' => 13,
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
            'position' => 14,
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
