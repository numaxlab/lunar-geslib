<?php

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
    public const PRODUCT_TYPE_ID = 1;
    public const DEFAULT_STATUS = 'published';

    public function __construct(private readonly bool $isEbook = false) {}

    public function __invoke(Article $article): void
    {
        if ($article->action()->isDelete()) {
            $variant = ProductVariant::where('sku', $article->id())->first();

            if ($variant) {
                $variant->product->delete();
                $variant->delete();
            }

            return;
        }

        $variant = ProductVariant::where('sku', $article->id())->first();

        $languageCollectionGroup = CollectionGroup::where('handle', LanguageCommand::HANDLE)->firstOrFail();

        $originalLanguageCollection = Collection::where(
            'attribute_data->geslib-code->value',
            $article->originalLanguageId(),
        )->where('collection_group_id', $languageCollectionGroup->id)->first();

        $productAttributeData = [
            // book-main group
            'name' => new Text($article->title()),
            'subtitle' => new Text($article->subtitle()),
            'created-at' => new Date($article->createdAt()?->format('Y-m-d')),
            'novelty-date' => new Date($article->noveltyDate()?->format('Y-m-d')),
            // bibliographic-data group
            'issue-date' => new Date($article->edition()?->date()?->format('Y-m-d')),
            'first-issue-year' => new Number($article->firstEditionYear()),
            'edition-number' => new Number($article->edition()?->number()),
            'reissue-date' => new Date($article->edition()?->reEditionDate()?->format('Y-m-d')),
            'last-issue-year' => new Number($article->lastEditionYear()),
            'edition-origin' => new Text($article->edition()?->editorial()),
            'original-title' => new Text($article->originalTitle()),
            'original-language' => new Text(
                $originalLanguageCollection ?
                    $originalLanguageCollection->attribute_data->get('name')->getValue() :
                    null,
            ),
            'pages' => new Number($article->pagesQty()),
            'illustrations-quantity' => new Number($article->illustrationsQty()),
        ];

        $brand = Brand::where('attribute_data->geslib-code->value', $article->editorialId())->first();

        if (!$variant) {
            $product = Product::create([
                'product_type_id' => self::PRODUCT_TYPE_ID,
                'brand_id' => $brand?->id,
                'status' => self::DEFAULT_STATUS,
                'attribute_data' => $productAttributeData,
            ]);

            $variant = ProductVariant::create([
                'product_id' => $product->id,
                'tax_class_id' => config('lunar.geslib.product_types_taxation.' . $article->typeId(), 1),
                'tax_ref' => $article->taxes(),
                'sku' => $article->id(),
                'gtin' => $article->isbn(),
                'ean' => $article->ean(),
                'width_value' => $article->width(),
                'width_unit' => config('lunar.geslib.measurements.width_unit', 'mm'),
                'height_value' => $article->height(),
                'height_unit' => config('lunar.geslib.measurements.height_unit', 'mm'),
                'weight_value' => $article->weight(),
                'weight_unit' => config('lunar.geslib.measurements.weight_unit', 'g'),
                'shippable' => !$this->isEbook,
                'stock' => $article->stock() ?? 0,
                'unit_quantity' => 1,
                'min_quantity' => 1,
                'quantity_increment' => 1,
                'backorder' => 0,
                'purchasable' => in_array(
                    $article->statusId(),
                    config('lunar.geslib.not_purchasable_statuses', []),
                ) ? 'in_stock' : 'always',
            ]);

            $variant->prices()->create([
                'price' => $article->priceWithoutTaxes(),
                'compare_price' => $article->referencePrice(),
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
                'tax_class_id' => config('lunar.geslib.product_types_taxation.' . $article->typeId(), 1),
                'tax_ref' => $article->taxes(),
                'gtin' => $article->isbn(),
                'ean' => $article->ean(),
                'width_value' => $article->width(),
                'height_value' => $article->height(),
                'weight_value' => $article->weight(),
                'stock' => $article->stock() ?? 0,
            ]);

            $variant->prices()->first()->update([
                'price' => $article->priceWithoutTaxes(),
                'compare_price' => $article->referencePrice(),
            ]);

            GeslibArticleUpdated::dispatch($variant);
        }

        // Product type collection
        $group = CollectionGroup::where('handle', TypeCommand::HANDLE)->firstOrFail();
        $statusCollection = Collection::where('attribute_data->geslib-code->value', $article->typeId())
            ->where('collection_group_id', $group->id)->get();

        (new CollectionGroupSync($product, $group->id, $statusCollection))->handle();

        // Status collection
        $group = CollectionGroup::where('handle', TypeCommand::HANDLE)->firstOrFail();
        $statusCollection = Collection::where('attribute_data->geslib-code->value', $article->statusId())
            ->where('collection_group_id', $group->id)->get();

        (new CollectionGroupSync($product, $group->id, $statusCollection))->handle();

        // Language collection
        $languageCollection = Collection::where('attribute_data->geslib-code->value', $article->languageId())
            ->where('collection_group_id', $languageCollectionGroup->id)->get();

        (new CollectionGroupSync($product, $languageCollectionGroup->id, $languageCollection))->handle();
    }
}
