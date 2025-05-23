<?php

namespace NumaxLab\Lunar\Geslib\Geslib;

use Lunar\FieldTypes\Number;
use Lunar\FieldTypes\Text;
use Lunar\Models\Brand;
use Lunar\Models\Product;
use Lunar\Models\ProductVariant;
use NumaxLab\Geslib\Lines\Article;
use NumaxLab\Lunar\Geslib\FieldTypes\Date;

class ArticleCommand
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
        } else {
            $variant = ProductVariant::where('sku', $article->id())->first();

            $productAttributeData = [
                // book-main
                'name' => new Text($article->title()),
                'subtitle' => new Text($article->subtitle()),
                'created-at' => new Date($article->createdAt()),
                'novelty-date' => new Date($article->noveltyDate()),
                // bibliographic-data
                'issue-date' => new Date($article->edition()?->date()),
                'first-issue-year' => new Number($article->firstEditionYear()),
                'edition-number' => new Number($article->edition()?->number()),
                'reissue-date' => new Date($article->edition()?->reEditionDate()),
                'last-issue-year' => new Number($article->lastEditionYear()),
                //'edition-origin',
                'pages' => new Number($article->pagesQty()),
                //'illustrations-quantity',
            ];

            $editorial = Brand::where('attribute_data->geslib-code->value', $article->editorialId())->first();

            if (!$variant) {
                $product = Product::create([
                    'product_type_id' => self::PRODUCT_TYPE_ID,
                    'brand_id' => $editorial?->id,
                    'status' => self::DEFAULT_STATUS,
                    'attribute_data' => $productAttributeData,
                ]);

                $variant = ProductVariant::create([
                    'product_id' => $product->id,
                    'tax_class_id' => 1,
                    'unit_quantity' => 1,
                    'min_quantity' => 1,
                    'quantity_increment' => 1,
                    'sku' => $article->id(),
                    'gtin' => $article->isbn(),
                    'ean' => $article->ean(),
                    'width_value' => $article->width(),
                    'width_unit' => 'mm',
                    'height_value' => $article->height(),
                    'height_unit' => 'mm',
                    'weight_value' => $article->weight(),
                    'weight_unit' => 'g',
                    'shippable' => !$this->isEbook,
                    'stock' => $article->stock() ?? 0,
                    'backorder' => 0,
                    'purchasable' => true,
                ]);

                $variant->prices()->create([
                    'price' => $article->priceWithoutTaxes(),
                    'currency_id' => 1,
                    'min_quantity' => 1,
                    'customer_group_id' => null,
                ]);
            } else {
                $variant->product->update([
                    'brand_id' => $editorial?->id,
                    'attribute_data' => $productAttributeData,
                ]);

                $variant->update([
                    'gtin' => $article->isbn(),
                    'ean' => $article->ean(),
                    'width_value' => $article->width(),
                    'height_value' => $article->height(),
                    'weight_value' => $article->weight(),
                    'stock' => $article->stock() ?? 0,
                ]);

                $variant->prices()->first()->update([
                    'price' => $article->priceWithoutTaxes(),
                ]);
            }
        }
    }
}
