<?php

namespace App\Models;

use App\Enums\CareVisitDeliveryStatus;
use App\Enums\CareVisitType;
use Carbon\CarbonImmutable;
use Database\Factories\CareVisitFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use InvalidArgumentException;

/**
 * A planned visit, where a CareWorker will visit a ServiceUser and provide care services.
 *
 * @property-read int $id
 * @property CareVisitType $type
 * @property int $care_worker_id
 * @property int $service_user_id
 * @property CarbonImmutable $start
 * @property CarbonImmutable $finish
 * @property CareVisitDeliveryStatus $delivery_status The status of the visit
 * @property CarbonImmutable|null $cancelled_at When the visit was cancelled
 * @property CarbonImmutable|null $arrival_at When the CareWorker arrived
 * @property float|null $arrival_lat The latitude of the CareWorker when they arrived
 * @property float|null $arrival_lng The longitude of the CareWorker when they arrived
 * @property CarbonImmutable|null $departure_at When the CareWorker departed
 * @property float|null $departure_lat The latitude of the CareWorker when they departed
 * @property float|null $departure_lng The longitude of the CareWorker when they departed
 * @property-read CarbonImmutable $created_at
 * @property-read CarbonImmutable $updated_at
 *
 * @property-read int $duration_minutes
 *
 * @property-read ServiceUser $serviceUser
 * @property-read CareWorker $careWorker
 *
 * @method static CareVisitFactory factory(...$parameters)
 */
class CareVisit extends Model
{
    use HasFactory;

    protected static function booted(): void
    {
        static::saving(function (self $visit) {
            // Ensure the start time is before the finish time
            if ($visit->start->greaterThanOrEqualTo($visit->finish)) {
                throw new InvalidArgumentException('The start time must be before the finish time.');
            }

            switch ($visit->delivery_status) {
                // If the visit is pending, arrival and departure timestamps should not be set
                case CareVisitDeliveryStatus::Pending:
                    if (isset($visit->arrival_at, $visit->departure_at)) {
                        throw new InvalidArgumentException('Arrival and departure timestamps are not allowed for pending visits.');
                    }
                    break;

                // If the visit is delivered, arrival and departure timestamps are required
                case CareVisitDeliveryStatus::Delivered:
                    if (!isset($visit->arrival_at, $visit->departure_at)) {
                        throw new InvalidArgumentException('Arrival and departure timestamps are required for delivered visits.');
                    }

                    if (!isset($visit->arrival_lat, $visit->arrival_lng, $visit->departure_lat, $visit->departure_lng)) {
                        throw new InvalidArgumentException('Arrival and departure locations are required for delivered visits.');
                    }

                    break;

                // If the visit is frustrated, arrival timestamp is required and departure timestamp is not allowed
                case CareVisitDeliveryStatus::Frustrated:
                    if (!isset($visit->arrival_at)) {
                        throw new InvalidArgumentException('Arrival and departure timestamps are required for frustrated visits.');
                    }
                    if (isset($visit->departure_at)) {
                        throw new InvalidArgumentException('Departure timestamp is not required for frustrated visits.');
                    }
                    break;

                // If the visit is cancelled, cancelled_at timestamp is required
                case CareVisitDeliveryStatus::Cancelled:
                    if (!isset($visit->cancelled_at)) {
                        throw new InvalidArgumentException('Cancelled timestamp is required for cancelled visits.');
                    }
                    break;
            }
        });
    }

    protected function casts(): array
    {
        return [
            'type' => CareVisitType::class,
            'start' => 'immutable_datetime',
            'finish' => 'immutable_datetime',
            'delivery_status' => CareVisitDeliveryStatus::class,
            'cancelled_at' => 'immutable_datetime',
            'arrival_at' => 'immutable_datetime',
            'arrival_lat' => 'float',
            'arrival_lng' => 'float',
            'departure_at' => 'immutable_datetime',
            'departure_lat' => 'float',
            'departure_lng' => 'float',
        ];
    }

    // region Relationships

    /**
     * The CareWorker who is attending the visit.
     *
     * @return BelongsTo<CareWorker>
     */
    public function careWorker(): BelongsTo
    {
        return $this->belongsTo(CareWorker::class);
    }

    /**
     * The ServiceUser who is being visited.
     *
     * @return BelongsTo<ServiceUser>
     */
    public function serviceUser(): BelongsTo
    {
        return $this->belongsTo(ServiceUser::class);
    }

    // endregion Relationships

    // region Attributes

    /**
     * Calculate the duration of the visit in minutes
     */
    protected function durationMinutes(): Attribute
    {
        return Attribute::get(fn () => $this->start->diffInMinutes($this->finish));
    }

    // endregion Attributes
}
