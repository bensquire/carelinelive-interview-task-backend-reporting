<?php

namespace Tests\Feature;

use App\Enums\CareVisitDeliveryStatus;
use App\Models\CareVisit;
use App\Models\CareWorker;
use App\Models\ServiceUser;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CareVisitTest extends TestCase
{
    use LazilyRefreshDatabase;

    #[Test]
    public function it_can_create_pending_care_visits(): void
    {
        $serviceUser = ServiceUser::factory()->create();
        $careWorker = CareWorker::factory()->create();

        $visit = CareVisit::factory()
            ->serviceUser($serviceUser)
            ->careWorker($careWorker)
            ->create();

        $this->assertDatabaseHas(CareVisit::class, [
            'id' => $visit->id,
            'service_user_id' => $serviceUser->id,
            'care_worker_id' => $careWorker->id,
            'start' => $visit->start,
            'finish' => $visit->finish,
            'delivery_status' => CareVisitDeliveryStatus::Pending,
        ]);
    }

    #[Test]
    public function it_calculates_duration(): void
    {
        $visit = CareVisit::factory()->create([
            'start' => '2021-01-01 12:00:00',
            'finish' => '2021-01-01 12:30:00',
        ]);

        $this->assertEquals(30, $visit->duration_minutes);
    }

    #[Test]
    public function it_requires_start_time_to_be_before_finish_time(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The start time must be before the finish time.');

        CareVisit::factory()->create([
            'start' => '2021-01-01 12:00:00',
            'finish' => '2021-01-01 11:00:00',
        ]);
    }

    #[Test]
    public function it_requires_arrival_and_departure_timestamps_for_delivered_visits(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Arrival and departure timestamps are required for delivered visits.');

        CareVisit::factory()->create([
            'delivery_status' => CareVisitDeliveryStatus::Delivered,
        ]);
    }

    #[Test]
    public function it_requires_arrival_and_departure_locations_for_delivered_visits(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Arrival and departure locations are required for delivered visits.');

        CareVisit::factory()->make([
            'delivery_status' => CareVisitDeliveryStatus::Delivered,
            'arrival_at' => now(),
            'departure_at' => now(),
        ])
            // The factory will automatically populate the location attributes for delivered visits, so we need to clear them manually.
            ->forceFill([
                'arrival_lat' => null,
                'arrival_lng' => null,
                'departure_lat' => null,
                'departure_lng' => null,
            ])
            ->save();
    }

    #[Test]
    public function it_requires_arrival_timestamp_for_frustrated_visits(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Arrival and departure timestamps are required for frustrated visits.');

        CareVisit::factory()->create([
            'delivery_status' => CareVisitDeliveryStatus::Frustrated,
        ]);
    }

    #[Test]
    public function it_doesnt_allow_departure_timestamp_for_frustrated_visits(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Departure timestamp is not required for frustrated visits.');

        CareVisit::factory()->create([
            'delivery_status' => CareVisitDeliveryStatus::Frustrated,
            'arrival_at' => now(),
            'departure_at' => now(),
        ]);
    }

    #[Test]
    public function it_requires_cancelled_at_for_cancelled_visits(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Cancelled timestamp is required for cancelled visits.');

        CareVisit::factory()->create([
            'delivery_status' => CareVisitDeliveryStatus::Cancelled,
        ]);
    }
}
