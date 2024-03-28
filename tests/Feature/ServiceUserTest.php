<?php

namespace Tests\Feature;

use App\Models\ServiceUser;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ServiceUserTest extends TestCase
{
    use LazilyRefreshDatabase;

    #[Test]
    public function it_can_create_service_users(): void
    {
        $serviceUser = ServiceUser::factory()->create();

        $this->assertDatabaseHas(ServiceUser::class, [
            'id' => $serviceUser->id,
            'name' => $serviceUser->name,
            'location_lat' => $serviceUser->location_lat,
            'location_lng' => $serviceUser->location_lng,
        ]);
    }
}
