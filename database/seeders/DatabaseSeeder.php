<?php

namespace Database\Seeders;

use App\Enums\CareVisitDeliveryStatus;
use App\Models\CareVisit;
use App\Models\CareWorker;
use App\Models\ServiceUser;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * @return CareVisitDeliveryStatus
     */
    private function randomWeightedCareVisitDeliveryStatus(): CareVisitDeliveryStatus {
        $weightedStatuses = [
            CareVisitDeliveryStatus::Pending, CareVisitDeliveryStatus::Pending, // Twice as likely
            CareVisitDeliveryStatus::Delivered, CareVisitDeliveryStatus::Delivered, // Twice as likely
            CareVisitDeliveryStatus::Cancelled, // One times as likely
            CareVisitDeliveryStatus::Frustrated, // One times as likely
        ];

        // Select a random index
        $randomIndex = rand(0, count($weightedStatuses) - 1);

        // Return the selected status
        return $weightedStatuses[$randomIndex];
    }

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create care workers
        CareWorker::factory(10)->create();

        // Create service users
        $serviceUsers = ServiceUser::factory(20)->create();

        // Create care visits
        $serviceUsers->each(function ($serviceUser) {
            $numberOfVisitsForServiceUser = rand(30, 50);

            for ($i = 0; $i < $numberOfVisitsForServiceUser; $i++) {
                $deliveryStatus = $this->randomWeightedCareVisitDeliveryStatus();

                $careVisitFactory = CareVisit::factory()->state([
                    'service_user_id' => $serviceUser->id,
                    'care_worker_id' => CareWorker::inRandomOrder()->first()->id,
                    'delivery_status' => $deliveryStatus->value,
                ]);

                switch ($deliveryStatus) {
                    case CareVisitDeliveryStatus::Delivered:
                        $careVisitFactory->delivered()->create();
                        break;
                    case CareVisitDeliveryStatus::Cancelled:
                        $careVisitFactory->cancelled()->create();
                        break;
                    case CareVisitDeliveryStatus::Frustrated:
                        $careVisitFactory->frustrated()->create();
                        break;
                    default:
                        $careVisitFactory->create();
                        break;
                }
            }
        });
    }
}
