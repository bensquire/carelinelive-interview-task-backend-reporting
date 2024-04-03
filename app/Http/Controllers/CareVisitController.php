<?php

namespace App\Http\Controllers;

use App\Enums\CareVisitDeliveryStatus;
use App\Enums\Punctuality;
use App\Models\CareVisit;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;

class CareVisitController
{
    /**
     * Note: This endpoint is basically complete with validation middleware, feature tests
     * and all applicable filters applied.
     */
    public function totalVisits(Request $request): JsonResponse
    {
        $year = $request->query('year');
        $month = $request->query('month');
        $day = $request->query('day');
        $type = $request->query('type');
        $status = $request->query('status');
        $durationMinutes = $request->query('duration-minutes');
        $durationOperator = $request->query('duration-operator');
        $punctuality = $request->query('punctuality');

        $query = CareVisit::query();

        if ($year) {
            $query->whereYear('start', $year);
        }

        if ($month) {
            $query->whereMonth('start', $month);
        }

        if ($day) {
            $query->whereDay('start', $day);
        }

        if ($type) {
            $query->where('type', $type);
        }

        if ($status) {
            $query->where('delivery_status', $status);
        }

        if ($durationMinutes) {
            $durationMinutes = (int)$request->input('duration-minutes');
            $query->whereRaw("((strftime('%s', departure_at) - strftime('%s', arrival_at)) / 60) $durationOperator ?", [$durationMinutes]);
        }

        if ($punctuality) {
            $thresholds = Config::get('visit.punctuality_thresholds');

            switch ($punctuality) {
                case Punctuality::OnTime->value:
                    $query->whereRaw("ABS(strftime('%s', arrival_at) - strftime('%s', start)) <= ?", [$thresholds['on_time'] * 60]);
                    break;

                case Punctuality::Late->value:
                    $query->whereRaw("ABS(strftime('%s', arrival_at) - strftime('%s', start)) > ?", [$thresholds['on_time'] * 60])
                        ->whereRaw("ABS(strftime('%s', arrival_at) - strftime('%s', start)) <= ?", [$thresholds['late'] * 60]);
                    break;

                case Punctuality::Missed->value:
                    // Note convinced this is correct.
                    $query->whereNull('arrival_at')
                        ->where('start', '<', Carbon::now());
                    break;
            }
        }

        $totalVisits = $query->count();

        return response()->json(['totalVisits' => $totalVisits]);
    }

    /**
     * Note: This was started pre-chat, but post chat I've decided to leave it as is. I'm
     * leaving it here so demonstrate the ability to calculate averageDuration.
     * - Obviously as an absolute minimum the validation middleware would need to ensure the delivery_status = delivered
     */
    public function averageDuration(Request $request): JsonResponse
    {
        $year = $request->query('year');
        $month = $request->query('month');
        $day = $request->query('day');
        $type = $request->query('type');

        $query = CareVisit::query();
        $query->where('delivery_status', CareVisitDeliveryStatus::Delivered);

        if ($year) {
            $query->whereYear('arrival_at', $year);
        }

        if ($month) {
            $query->whereMonth('arrival_at', $month);
        }

        if ($day) {
            $query->whereDay('arrival_at', $day);
        }

        if ($type) {
            $query->where('type', $type);
        }

        $averageDuration = $query
            ->select(CareVisit::raw("AVG(strftime('%s', departure_at) - strftime('%s', arrival_at)) as average_duration"))
            ->value('average_duration');
        $averageDurationInMinutes = $averageDuration / 60; // Convert seconds to minutes

        return response()->json(['averageDurationInMinutes' => $averageDurationInMinutes]);
    }

    /**
     * Note: This was added post-chat, so-as to demonstrate how a combination of abstract and concrete classes
     * could be used for each of the different statistics required. If this was to be expanded we would have an
     * additional class for each of the other 4 statistics.
     */
    public function punctuality(Request $request): JsonResponse
    {
        $year = $request->query('year');
        $thresholds = Config::get('visit.punctuality_thresholds');

        $queryBuilder = new \App\Service\CareVisitStatistics\Punctuality($year, $thresholds['on_time'] * 60, $thresholds['late'] * 60);

        $query = $queryBuilder->buildQuery();

        // TODO: Some other service to actually perform the query and manipulate the data
        // TODO: And possibly another service/helper to put the manipulated data into a suitable format (JSON, CSV)?
        $result = $query->first();

        return response()->json([
            'values' => [
                'OnTime' => $result->OnTime ?? 0,
                'Late' => $result->Late ?? 0,
                'Missed' => $result->Missed ?? 0,
            ]
        ]);
    }
}
