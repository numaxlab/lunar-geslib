<?php

declare(strict_types=1);

namespace NumaxLab\Lunar\Geslib\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use NumaxLab\Lunar\Geslib\Models\GeslibInterFile;

class GeslibInterFileFactory extends Factory
{
    protected $model = GeslibInterFile::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->numerify('INTER###.zip'),
            'status' => GeslibInterFile::STATUS_PENDING,
            'received_at' => now(),
            'started_at' => null,
            'finished_at' => null,
            'total_lines' => 0,
            'processed_lines' => 0,
            'log' => null,
        ];
    }
}
