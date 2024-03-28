<?php

namespace Tests\Feature;

use App\Models\CareWorker;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CareWorkerTest extends TestCase
{
    use LazilyRefreshDatabase;

    #[Test]
    public function it_can_create_care_workers(): void
    {
        $careWorker = CareWorker::factory()->create();

        $this->assertDatabaseHas(CareWorker::class, [
            'id' => $careWorker->id,
            'name' => $careWorker->name,
        ]);
    }
}
