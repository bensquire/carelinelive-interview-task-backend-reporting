<?php

namespace App\Service\CareVisitStatistics;

use \Illuminate\Database\Eloquent\Builder;

class Punctuality extends QueryBuilderAbstract implements QueryBuilderInterface
{
    protected int $onTimeThresholdInSeconds;
    protected int $lateThresholdInSeconds;

    function __construct(int $year, int $onTimeThresholdInSeconds, int $lateThresholdInSeconds)
    {
        parent::__construct($year);
        $this->setOnTimeThresholdInSeconds($onTimeThresholdInSeconds);
        $this->setLateThresholdInSeconds($lateThresholdInSeconds);
    }

    public function setOnTimeThresholdInSeconds(int $onTimeThresholdInSeconds): self
    {
        $this->onTimeThresholdInSeconds = $onTimeThresholdInSeconds;
        return $this;
    }

    public function setLateThresholdInSeconds(int $lateThresholdInSeconds): self
    {
        $this->lateThresholdInSeconds = $lateThresholdInSeconds;
        return $this;
    }

    public function buildQuery(): Builder
    {
        $this->filterData();
        $this->query->selectRaw("
                SUM(CASE
                    WHEN strftime('%s', arrival_at) - strftime('%s', start) <= ? THEN 1
                    ELSE 0
                END) AS OnTime,
                SUM(CASE
                    WHEN strftime('%s', arrival_at) - strftime('%s', start) > ? AND strftime('%s', arrival_at) - strftime('%s', start) <= ? THEN 1
                    ELSE 0
                END) AS Late,
                SUM(CASE
                    WHEN arrival_at IS NULL AND start < datetime('now') THEN 1
                    ELSE 0
                END) AS Missed
            ", [$this->onTimeThresholdInSeconds, $this->onTimeThresholdInSeconds, $this->lateThresholdInSeconds]);

        return $this->query;
    }
}
