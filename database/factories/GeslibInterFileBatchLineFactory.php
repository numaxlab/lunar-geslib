<?php

declare(strict_types=1);

namespace NumaxLab\Lunar\Geslib\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use NumaxLab\Geslib\Lines\ArticleAuthor;
use NumaxLab\Lunar\Geslib\Models\GeslibInterFile;
use NumaxLab\Lunar\Geslib\Models\GeslibInterFileBatchLine;

class GeslibInterFileBatchLineFactory extends Factory
{
    protected $model = GeslibInterFileBatchLine::class;

    public function definition(): array
    {
        return [
            'geslib_inter_file_id' => GeslibInterFile::factory(),
            'line_type' => ArticleAuthor::CODE,
            'article_id' => 1,
            'data' => [],
        ];
    }
}
