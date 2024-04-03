<?php

namespace Tests\Feature\Middleware\CareVisit;

use App\Enums\CareVisitDeliveryStatus;
use App\Enums\Punctuality;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ValidateTotalVisitsTest extends TestCase
{
    #[Test]
    public function test_validation_passes_with_valid_input()
    {
        $response = $this->get('/api/total-visits?year=2023&month=3&day=1&type=medication&status=delivered');
        $response->assertStatus(200);
    }

    #[Test]
    public function test_validation_middleware_returns_valid_error_for_missing_year()
    {
        $response = $this->get('/api/total-visits');

        $response->assertStatus(422);
        $response->assertJson(['year' => ['The year field is required.']]);
    }

    #[Test]
    public function test_validation_middleware_returns_valid_error_for_invalid_year()
    {
        $response = $this->get('/api/total-visits?year=1999');

        $response->assertStatus(422);
        $response->assertJson(['year' => ['The year field must be at least 2017.']]);
    }

    #[Test]
    public function test_validation_middleware_returns_valid_error_for_invalid_month()
    {
        $response = $this->get('/api/total-visits?year=2023&month=13');

        $response->assertStatus(422);
        $response->assertJson(['month' => ['The month field must not be greater than 12.']]);
    }

    #[Test]
    public function test_validation_middleware_returns_valid_error_for_invalid_day()
    {
        $response = $this->get('/api/total-visits?year=2023&month=4&day=32');

        $response->assertStatus(422);
        $response->assertJson(['day' => ['The day field must not be greater than 31.']]);
    }

    #[Test]
    public function test_validation_status()
    {
        $response = $this->get('/api/total-visits?year=2023&duration-minutes=30&duration-operator=>');

        $response->assertStatus(422);
        $response->assertJson(['status' => ['The status field is required.', 'The status must be "delivered" if "duration-minutes" is specified.']]);
    }

    #[Test]
    public function test_validation_invalid_type()
    {
        $response = $this->get('/api/total-visits?year=2023&type=invalid_type');

        $response->assertStatus(422);
        $response->assertJson(['type' => ['The selected type is invalid.']]);
    }

    #[Test]
    public function test_validation_status_required_when_punctuality_not_missed()
    {
        $response = $this->get('/api/total-visits?year=2023&punctuality=' . Punctuality::OnTime->value . '&status=' . CareVisitDeliveryStatus::Cancelled->value);

        $response->assertStatus(422);
        $response->assertJson(['punctuality' => ['The status must be "delivered" or "frustrated" if "punctuality" is specified.']]);
    }

    #[Test]
    public function test_validation_status_not_needed_when_punctuality_missed()
    {
        $response = $this->get('/api/total-visits?year=2023&punctuality=' . Punctuality::Missed->value);

        $response->assertStatus(200);
    }
}
