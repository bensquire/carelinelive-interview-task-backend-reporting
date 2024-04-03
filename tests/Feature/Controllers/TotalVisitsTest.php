<?php

namespace Tests\Feature\Controllers;

use App\Enums\CareVisitDeliveryStatus;
use App\Enums\CareVisitType;
use App\Enums\Punctuality;
use App\Models\CareVisit;
use App\Models\CareWorker;
use App\Models\ServiceUser;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class TotalVisitsTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_can_perform_simple_count_given_the_year()
    {
        $serviceUser = ServiceUser::factory()->create();
        $careWorker = CareWorker::factory()->create();

        CareVisit::factory(5)
            ->serviceUser($serviceUser)
            ->careWorker($careWorker)
            ->create();

        $response = $this->getJson('/api/total-visits?year=' . now()->year);

        $response->assertOk();
        $response->assertJson(['totalVisits' => 5]);
    }

    #[Test]
    public function it_can_perform_simple_count_given_the_year_and_month()
    {
        $serviceUser = ServiceUser::factory()->create();
        $careWorker = CareWorker::factory()->create();

        // Part of result
        CareVisit::factory()
            ->serviceUser($serviceUser)
            ->careWorker($careWorker)
            ->create([
                'start' => Carbon::create(2023, 3, 10, 12),
                'finish' => Carbon::create(2023, 3, 10, 13)
            ]);

        // Not part of result
        CareVisit::factory()
            ->serviceUser($serviceUser)
            ->careWorker($careWorker)
            ->create([
                'start' => Carbon::create(2023, 4, 11, 12),
                'finish' => Carbon::create(2023, 4, 11, 13)
            ]);

        $response = $this->getJson('/api/total-visits?year=2023&month=3');

        $response->assertOk();
        $response->assertJson(['totalVisits' => 1]);
    }

    #[Test]
    public function it_can_perform_simple_count_given_the_year_month_and_day()
    {
        $serviceUser = ServiceUser::factory()->create();
        $careWorker = CareWorker::factory()->create();

        // Part of result
        CareVisit::factory()
            ->serviceUser($serviceUser)
            ->careWorker($careWorker)
            ->create([
                'start' => Carbon::create(2023, 3, 10, 12),
                'finish' => Carbon::create(2023, 3, 10, 13)
            ]);

        // Not part of result (to prove it works)
        CareVisit::factory()
            ->serviceUser($serviceUser)
            ->careWorker($careWorker)
            ->create([
                'start' => Carbon::create(2023, 4, 11, 12),
                'finish' => Carbon::create(2023, 4, 11, 13)
            ]);

        $response = $this->getJson('/api/total-visits?year=2023&month=3&day=10');

        $response->assertOk();
        $response->assertJson(['totalVisits' => 1]);
    }

    #[Test]
    public function it_can_perform_simple_count_given_a_type()
    {
        $serviceUser = ServiceUser::factory()->create();
        $careWorker = CareWorker::factory()->create();

        CareVisit::factory(5)
            ->serviceUser($serviceUser)
            ->careWorker($careWorker)
            ->create(['type' => CareVisitType::DomesticCare->value]);

        // Not part of result (to prove it works)
        CareVisit::factory()
            ->serviceUser($serviceUser)
            ->careWorker($careWorker)
            ->create(['type' => CareVisitType::PersonalCare->value]);

        $response = $this->getJson('/api/total-visits?year=' . now()->year . '&type=' . CareVisitType::DomesticCare->value);

        $response->assertOk();
        $response->assertJson(['totalVisits' => 5]);
    }

    #[Test]
    public function it_can_perform_simple_count_given_a_status()
    {
        $serviceUser = ServiceUser::factory()->create();
        $careWorker = CareWorker::factory()->create();

        CareVisit::factory(5)
            ->serviceUser($serviceUser)
            ->careWorker($careWorker)
            ->create([
                'delivery_status' => CareVisitDeliveryStatus::Cancelled,
                'cancelled_at' => now(),
            ]);

        // Not part of result (to prove it works)
        CareVisit::factory()
            ->serviceUser($serviceUser)
            ->careWorker($careWorker)
            ->create();

        $response = $this->getJson('/api/total-visits?year=' . now()->year . '&status=' . CareVisitDeliveryStatus::Cancelled->value);

        $response->assertOk();
        $response->assertJson(['totalVisits' => 5]);
    }

    #[Test]
    public function it_can_perform_simple_count_for_a_duration()
    {
        $serviceUser = ServiceUser::factory()->create();
        $careWorker = CareWorker::factory()->create();

        CareVisit::factory(5)
            ->serviceUser($serviceUser)
            ->careWorker($careWorker)
            ->create([
                'delivery_status' => CareVisitDeliveryStatus::Delivered,
                'start' => Carbon::create(2023, 3, 10, 12),
                'finish' => Carbon::create(2023, 3, 10, 13),
                'arrival_at' => Carbon::create(2023, 3, 10, 12, 02),
                'departure_at' => Carbon::create(2023, 3, 10, 12, 54),  // 52 minutes
            ]);

        // All the data
        $response = $this->getJson('/api/total-visits?year=2023&status=' . CareVisitDeliveryStatus::Delivered->value . '&duration-minutes=20&duration-operator=>=');
        $response->assertOk();
        $response->assertJson(['totalVisits' => 5]);

        // None of the data
        $response = $this->getJson('/api/total-visits?year=2023&status=' . CareVisitDeliveryStatus::Delivered->value . '&duration-minutes=20&duration-operator=<=');
        $response->assertOk();
        $response->assertJson(['totalVisits' => 0]);
    }

    #[Test]
    public function it_can_perform_simple_count_for_visits_punctuality_late()
    {
        $serviceUser = ServiceUser::factory()->create();
        $careWorker = CareWorker::factory()->create();

        CareVisit::factory(5)
            ->serviceUser($serviceUser)
            ->careWorker($careWorker)
            ->create([
                'delivery_status' => CareVisitDeliveryStatus::Delivered,
                'start' => Carbon::create(2023, 3, 10, 12),
                'finish' => Carbon::create(2023, 3, 10, 13),
                'arrival_at' => Carbon::create(2023, 3, 10, 12, 20), // Late
                'departure_at' => Carbon::create(2023, 3, 10, 12, 59),
            ]);

        // All the data
        $response = $this->getJson('/api/total-visits?year=2023&punctuality=' . Punctuality::Late->value . '&status=' . CareVisitDeliveryStatus::Delivered->value);
        $response->assertOk();
        $response->assertJson(['totalVisits' => 5]);

        // None of the data
        $response = $this->getJson('/api/total-visits?year=2023&punctuality=' . Punctuality::OnTime->value . '&status=' . CareVisitDeliveryStatus::Delivered->value);
        $response->assertOk();
        $response->assertJson(['totalVisits' => 0]);
    }

    #[Test]
    public function it_can_perform_simple_count_for_visits_punctuality_missed()
    {
        $serviceUser = ServiceUser::factory()->create();
        $careWorker = CareWorker::factory()->create();

        CareVisit::factory(5)
            ->serviceUser($serviceUser)
            ->careWorker($careWorker)
            ->create([
                'start' => Carbon::create(2022, 3, 10, 12), // Pushing start day back to 2022
                'finish' => Carbon::create(2022, 3, 10, 13),
            ]);

        // All the data
        $response = $this->getJson('/api/total-visits?year=2022&punctuality=' . Punctuality::Missed->value);
        $response->assertOk();
        $response->assertJson(['totalVisits' => 5]);

        // None of the data
        $response = $this->getJson('/api/total-visits?year=2022&punctuality=' . Punctuality::OnTime->value . '&status=' . CareVisitDeliveryStatus::Delivered->value);
        $response->assertOk();
        $response->assertJson(['totalVisits' => 0]);
    }
}
