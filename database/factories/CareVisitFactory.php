<?php

namespace Database\Factories;

use App\Enums\CareVisitDeliveryStatus;
use App\Enums\CareVisitType;
use App\Models\CareVisit;
use App\Models\CareWorker;
use App\Models\ServiceUser;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Lottery;

class CareVisitFactory extends Factory
{
    protected $model = CareVisit::class;

    public function configure(): self
    {
        return $this->afterMaking(function (CareVisit $visit) {
            if ($visit->delivery_status !== CareVisitDeliveryStatus::Delivered) {
                return;
            }

            // If the visit is delivered, fake the arrival and departure locations based on the service user's location.
            $visit->forceFill([
                'arrival_lat' => $this->faker->latitude(
                    $visit->serviceUser->location_lat - 0.001,
                    $visit->serviceUser->location_lat + 0.001
                ),

                'arrival_lng' => $this->faker->longitude(
                    $visit->serviceUser->location_lng - 0.001,
                    $visit->serviceUser->location_lng + 0.001
                ),

                'departure_lat' => $this->faker->latitude(
                    $visit->serviceUser->location_lat - 0.001,
                    $visit->serviceUser->location_lat + 0.001
                ),

                'departure_lng' => $this->faker->longitude(
                    $visit->serviceUser->location_lng - 0.001,
                    $visit->serviceUser->location_lng + 0.001
                ),
            ]);
        });
    }

    public function definition(): array
    {
        // Figure out an appropriate start and finish time for the visit.
        $start = CarbonImmutable::instance($this->faker->dateTimeThisMonth())
            ->startOfMinute()
            ->roundMinute(15);

        $finish = $start->addMinutes($this->faker->randomElement([
            15,
            30,
            45,
            60,
            75,
            90,
            105,
            120,
        ]));

        return [
            'type' => $this->faker->randomElement(CareVisitType::class),
            'care_worker_id' => CareWorkerFactory::new(),
            'service_user_id' => ServiceUserFactory::new(),
            'start' => $start,
            'finish' => $finish,
        ];
    }

    public function careWorker(CareWorker|CareWorkerFactory $careWorker): self
    {
        return $this->state([
            'care_worker_id' => $careWorker,
        ]);
    }

    public function serviceUser(ServiceUser|ServiceUserFactory $serviceUser): self
    {
        return $this->state([
            'service_user_id' => $serviceUser,
        ]);
    }

    public function cancelled(): self
    {
        return $this->state(fn(array $attributes) => [
            'delivery_status' => CareVisitDeliveryStatus::Cancelled,
            'cancelled_at' => $this->faker->dateTimeBetween(
                startDate: now()->subMonth(),
                endDate: $attributes['start']
            ),
        ]);
    }

    public function frustrated(): self
    {
        return $this->state(function (array $attributes) {
            // Between an hour before and an hour after the start time.
            $arrival = $attributes['start']->toImmutable()->addMinutes($this->faker->numberBetween(-60, 60));

            return [
                'arrival_at' => $arrival
            ];
        });
    }

    public function delivered(): self
    {
        return $this->state(function (array $attributes) {
            // 1 in 5 chance that the visit is delivered on time.
            return Lottery::odds(1, 5)
                ->winner(function () use ($attributes) {
                    return [
                        'delivery_status' => CareVisitDeliveryStatus::Delivered,
                        'arrival_at' => $attributes['start'],
                        'departure_at' => $attributes['finish'],
                    ];
                })
                ->loser(function () use ($attributes) {
                    // Between an hour before and an hour after the start time.
                    $arrival = $attributes['start']->toImmutable()->addMinutes($this->faker->numberBetween(-60, 60));

                    // Between an hour before and an hour after the finish time.
                    $departure = $attributes['finish']->toImmutable()->addMinutes($this->faker->numberBetween(-60, 60));

                    // Departure should not be before the arrival.
                    $departure = max($arrival, $departure);

                    return [
                        'delivery_status' => CareVisitDeliveryStatus::Delivered,
                        'arrival_at' => $arrival,
                        'departure_at' => $departure,
                    ];
                })
                ->choose();
        });
    }
}
