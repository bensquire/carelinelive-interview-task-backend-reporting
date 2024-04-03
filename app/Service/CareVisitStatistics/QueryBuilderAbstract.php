<?php

namespace App\Service\CareVisitStatistics;

use App\Models\CareVisit;
use \Illuminate\Database\Eloquent\Builder;

abstract class QueryBuilderAbstract
{
    protected Builder $query;
    protected ?int $year = null;
    protected ?int $month = null;
    protected ?int $day = null;
    protected ?string $type = null;
    protected ?string $status = null;
    protected ?int $durationMinutes = null;
    protected ?string $durationOperator = '=';

    public function __construct(int $year)
    {
        $this->year = $year;
        $this->query = CareVisit::query();
    }

    public function setType(string $type): self
    {
        $this->type = $type;
        return $this;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;
        return $this;
    }

    public function setDuration(int $minutes, string $operator = '='): self
    {
        $this->durationMinutes = $minutes;
        $this->durationOperator = $operator;
        return $this;
    }

    protected function filterData(): void
    {
        if ($this->year) {
            $this->query->whereYear('start', $this->year);
        }

        if ($this->month) {
            $this->query->whereMonth('start', $this->month);
        }

        if ($this->day) {
            $this->query->whereDay('start', $this->day);
        }

        if ($this->type) {
            $this->query->where('type', $this->type);
        }

        if ($this->status) {
            $this->query->where('delivery_status', $this->status);
        }

        if ($this->durationMinutes) {
            $this->query->whereRaw("((strftime('%s', departure_at) - strftime('%s', arrival_at)) / 60) $this->durationOperator ?", [$this->durationMinutes]);
        }
    }
}
