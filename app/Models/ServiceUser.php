<?php

namespace App\Models;

use Carbon\CarbonImmutable;
use Database\Factories\ServiceUserFactory;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * A person who is receiving care services.
 *
 * @property-read int $id
 * @property string $name
 * @property float $location_lat
 * @property float $location_lng
 * @property-read CarbonImmutable $created_at
 * @property-read CarbonImmutable $updated_at
 * @property-read EloquentCollection<CareVisit> $careVisits
 *
 * @method static ServiceUserFactory factory(...$parameters)
 */
class ServiceUser extends Model
{
    use HasFactory;

    /**
     * Visits that this ServiceUser is scheduled to receive.
     *
     * @return HasMany<CareVisit>
     */
    public function careVisits(): HasMany
    {
        return $this->hasMany(CareVisit::class);
    }
}
