<?php

declare(strict_types=1);

namespace NumaxLab\Lunar\Geslib\InterCommands;

use Lunar\FieldTypes\Number;
use Lunar\FieldTypes\Text;
use Lunar\Models\Brand;
use Lunar\Models\Collection;
use Lunar\Models\CollectionGroup;
use Lunar\Models\Product;
use Lunar\Models\ProductVariant;
use NumaxLab\Geslib\Lines\Article;
use NumaxLab\Lunar\Geslib\Events\GeslibArticleCreated;
use NumaxLab\Lunar\Geslib\Events\GeslibArticleUpdated;
use NumaxLab\Lunar\Geslib\FieldTypes\Date;
use NumaxLab\Lunar\Geslib\Managers\CollectionGroupSync;

class ArticleCommand extends AbstractCommand
{
    public const int PRODUCT_TYPE_ID = 1;

    public const string DEFAULT_STATUS = 'published';

    public function __construct(private readonly Article $article, private readonly bool $isEbook = false) {}

    public function __invoke(): void
    {
        $variant = ProductVariant::where('sku', $this->article->id())->first();

        if ($this->article->action()->isDelete()) {
            if ($variant) {
                $variant->product->update([
                    'status' => 'draft',
                ]);
            }

            return;
        }

        $languageCollectionGroup = CollectionGroup::where('handle', LanguageCommand::HANDLE)->firstOrFail();

        $originalLanguageCollection = Collection::where('geslib_code', $this->article->originalLanguageId())
            ->where('collection_group_id', $languageCollectionGroup->id)
            ->first();

        $productAttributeData = [
            // book-main group
            'name' => new Text($this->article->title()),
            'subtitle' => new Text($this->article->subtitle()),
            'created-at' => new Date($this->article->createdAt()?->format('Y-m-d')),
            'novelty-date' => new Date($this->article->noveltyDate()?->format('Y-m-d')),
            // bibliographic-data group
            'issue-date' => new Date($this->article->edition()?->date()?->format('Y-m-d')),
            'first-issue-year' => new Number($this->article->firstEditionYear()),
            'edition-number' => new Text($this->article->edition()?->number()),
            'reissue-date' => new Date($this->article->edition()?->reEditionDate()?->format('Y-m-d')),
            'last-issue-year' => new Number($this->article->lastEditionYear()),
            'edition-origin' => new Text($this->article->edition()?->editorial()),
            'original-title' => new Text($this->article->originalTitle()),
            'original-language' => new Text(
                $originalLanguageCollection?->translateAttribute('name'),
            ),
            'pages' => new Number($this->article->pagesQty()),
            'illustrations-quantity' => new Number($this->article->illustrationsQty()),
        ];

        $brand = Brand::where('geslib_code', $this->article->editorialId())->first();

        if (! $variant) {
            $product = Product::create([
                'product_type_id' => self::PRODUCT_TYPE_ID,
                'brand_id' => $brand?->id,
                'status' => self::DEFAULT_STATUS,
                'attribute_data' => $productAttributeData,
            ]);

            $variant = ProductVariant::create([
                'product_id' => $product->id,
                'tax_class_id' => config('lunar.geslib.product_types_taxation.'.$this->article->typeId(), 1),
                'tax_ref' => $this->article->taxes(),
                'sku' => $this->article->id(),
                'gtin' => $this->article->isbn(),
                'ean' => $this->article->ean(),
                'width_value' => $this->article->width(),
                'width_unit' => config('lunar.geslib.measurements.width_unit', 'mm'),
                'height_value' => $this->article->height(),
                'height_unit' => config('lunar.geslib.measurements.height_unit', 'mm'),
                'weight_value' => $this->article->weight(),
                'weight_unit' => config('lunar.geslib.measurements.weight_unit', 'g'),
                'shippable' => ! $this->isEbook,
                'stock' => $this->article->stock() ?? 0,
                'unit_quantity' => 1,
                'min_quantity' => 1,
                'quantity_increment' => 1,
                'backorder' => 0,
                'purchasable' => in_array(
                    $this->article->statusId(),
                    config('lunar.geslib.not_purchasable_statuses', []),
                ) ? 'in_stock' : 'always',
            ]);

            $variant->prices()->create([
                'price' => $this->article->priceWithoutTaxes(),
                'compare_price' => $this->article->referencePrice(),
                'currency_id' => config('lunar.geslib.currency_id', 1),
                'min_quantity' => 1,
                'customer_group_id' => null,
            ]);

            GeslibArticleCreated::dispatch($variant);
        } else {
            $product = $variant->product;

            $product->update([
                'brand_id' => $brand?->id,
                'attribute_data' => array_merge($product->attribute_data->toArray(), $productAttributeData),
            ]);

            $variant->update([
                'tax_class_id' => config('lunar.geslib.product_types_taxation.'.$this->article->typeId(), 1),
                'tax_ref' => $this->article->taxes(),
                'gtin' => $this->article->isbn(),
                'ean' => $this->article->ean(),
                'width_value' => $this->article->width(),
                'height_value' => $this->article->height(),
                'weight_value' => $this->article->weight(),
                'stock' => $this->article->stock() ?? 0,
                'purchasable' => in_array(
                    $this->article->statusId(),
                    config('lunar.geslib.not_purchasable_statuses', []),
                ) ? 'in_stock' : 'always',
            ]);

            $variant->prices()->first()->update([
                'price' => $this->article->priceWithoutTaxes(),
                'compare_price' => $this->article->referencePrice(),
            ]);

            GeslibArticleUpdated::dispatch($variant);
        }

        // Product type collection
        $group = CollectionGroup::where('handle', TypeCommand::HANDLE)->firstOrFail();
        $productTypeCollection = Collection::where('geslib_code', $this->article->typeId())
            ->where('collection_group_id', $group->id)->get();

        new CollectionGroupSync($product, $group->id, $productTypeCollection)->handle();

        // Status collection
        $group = CollectionGroup::where('handle', StatusCommand::HANDLE)->firstOrFail();
        $statusCollection = Collection::where('geslib_code', $this->article->statusId())
            ->where('collection_group_id', $group->id)->get();

        new CollectionGroupSync($product, $group->id, $statusCollection)->handle();

        // Language collection
        $languageCollection = Collection::where('geslib_code', $this->article->languageId())
            ->where('collection_group_id', $languageCollectionGroup->id)->get();

        new CollectionGroupSync($product, $languageCollectionGroup->id, $languageCollection)->handle();
    }
}
