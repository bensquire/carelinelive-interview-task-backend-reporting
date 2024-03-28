<?php

namespace App\Models;

use Carbon\CarbonImmutable;
use Database\Factories\CareWorkerFactory;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * A worker who provides care to service users.
 *
 * @property-read int $id
 * @property string $name
 * @property-read CarbonImmutable $created_at
 * @property-read CarbonImmutable $updated_at
 * @property-read EloquentCollection<CareVisit> $careVisits
 *
 * @method static CareWorkerFactory factory(...$parameters)
 */
class CareWorker extends Model
{
    use HasFactory;

    /**
     * Visits that this care worker is attending.
     *
     * @return HasMany<CareVisit>
     */
    public function careVisits(): HasMany
    {
        return $this->hasMany(CareVisit::class);
    }
}
