<?php

namespace NumaxLab\Lunar\Geslib\Geslib;

use Lunar\FieldTypes\Number;
use Lunar\FieldTypes\Text;
use Lunar\Models\Product;
use NumaxLab\Geslib\Lines\Article;
use NumaxLab\Lunar\Geslib\FieldTypes\Date;

class ArticleCommand
{

    public function __invoke(Article $article): void
    {
        if ($article->action()->isDelete()) {
            $product = Product::where('attribute_data->geslib-code->value', $article->id())->first();

            if ($product) {
                $product->delete();
            }
        } else {
            $product = Product::where('attribute_data->geslib-code->value', $article->id())->first();

            if (!$product) {
                Product::create([
                    'product_type_id' => 1,
                    'status' => 'draft',
                    'attribute_data' => [
                        'geslib-code' => new Number($article->id()),
                        'isbn' => new Text($article->isbn()),
                        'ean' => new Text($article->ean()),
                        'title' => new Text($article->title()),
                        'subtitle' => new Text($article->subtitle()),
                        'created-at' => new Date($article->createdAt()),
                        'novelty-date' => new Date($article->noveltyDate()),
                        //BIBLIOGRAPHIC DATA
                        'issue-date' => new Date($article->edition()?->date()),
                        'first-issue-year' => new Number($article->firstEditionYear()),
                        'edition-number' => new Number($article->edition()?->number()),
                        'reissue-date' => new Date($article->edition()?->reEditionDate()),
                        'last-issue-year' => new Number($article->lastEditionYear()),
                        //'edition-origin',
                        'pages' => new Number($article->pagesQty()),
                        //'illustrations-quantity',
                        'weight' => new Number($article->weight()),
                        'width' => new Number($article->width()),
                        'height' => new Number($article->height()),
                    ]


                ]);
            } else {
                $product->update([
                    'product_type_id' => 1,
                    'status' => 'draft',
                    'attribute_data' => [
                        'geslib-code' => new Number($article->id()),
                        'isbn' => new Text($article->isbn()),
                        'ean' => new Text($article->ean()),
                        'title' => new Text($article->title()),
                        'subtitle' => new Text($article->subtitle()),
                        'created-at' => new Date($article->createdAt()),
                        'novelty-date' => new Date($article->noveltyDate()),
                        //BIBLIOGRAPHIC DATA
                        'issue-date' => new Date($article->edition()?->date()),
                        'first-issue-year' => new Number($article->firstEditionYear()),
                        'edition-number' => new Number($article->edition()?->number()),
                        'reissue-date' => new Date($article->edition()?->reEditionDate()),
                        'last-issue-year' => new Number($article->lastEditionYear()),
                        //'edition-origin',
                        'pages' => new Number($article->pagesQty()),
                        //'illustrations-quantity',
                        'weight' => new Number($article->weight()),
                        'width' => new Number($article->width()),
                        'height' => new Number($article->height()),
                    ]

                ]);
            }
        }
    }
}
