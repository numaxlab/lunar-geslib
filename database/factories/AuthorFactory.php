<?php

declare(strict_types=1);

namespace NumaxLab\Lunar\Geslib\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use NumaxLab\Lunar\Geslib\Models\Author;

class AuthorFactory extends Factory
{
    protected $model = Author::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'geslib_code' => $this->faker->unique()->numerify('AUTH######'),
            'attribute_data' => null,
        ];
    }
}
