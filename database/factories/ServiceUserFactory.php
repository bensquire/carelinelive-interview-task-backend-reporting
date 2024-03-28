<?php

namespace Database\Factories;

use App\Models\ServiceUser;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Factories\Factory;

class ServiceUserFactory extends Factory
{
    protected $model = ServiceUser::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'location_lat' => $this->faker->numberBetween(51.35, 51.7),
            'location_lng' => $this->faker->numberBetween(-0.59, 0.2),
        ];
    }
}
