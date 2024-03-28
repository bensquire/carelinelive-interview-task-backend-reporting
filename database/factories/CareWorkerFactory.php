<?php

namespace Database\Factories;

use App\Models\CareWorker;
use Illuminate\Database\Eloquent\Factories\Factory;

class CareWorkerFactory extends Factory
{
    protected $model = CareWorker::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
        ];
    }
}
