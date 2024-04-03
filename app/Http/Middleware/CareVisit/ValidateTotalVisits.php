<?php

namespace App\Http\Middleware\CareVisit;

use App\Enums\CareVisitDeliveryStatus;
use App\Enums\CareVisitType;
use App\Enums\Punctuality;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\Response;

class ValidateTotalVisits
{
    public function handle(Request $request, Closure $next): Response
    {
        $validator = Validator::make($request->query(), [
            'year' => 'required|digits:4|integer|min:2017|max:' . now()->year,
            'month' => 'nullable|integer|min:1|max:12',
            'day' => 'nullable|integer|min:1|max:31',
            'type' => [
                'nullable',
                'string',
                'in:' . implode(',', array_map(fn($case) => $case->value, CareVisitType::cases())),
            ],
            'status' => [
                'nullable',
                'string',
                Rule::requiredIf(fn() => $request->filled('duration-minutes')),
                'in:' . implode(',', array_map(fn($case) => $case->value, CareVisitDeliveryStatus::cases()))
            ],
            'duration-minutes' => [
                'nullable',
                'integer',
                'min:1',
                'max:360',    // 6 hours?
            ],
            'duration-operator' => [
                'string',
                Rule::requiredIf(function () use ($request) {
                    return $request['duration-minutes'] !== null;
                }),
                'in:=,<,>,>=,<=',
            ],
             'punctuality' => [
                 'nullable',
                 'string',
                 'in:' . implode(',', array_map(fn($case) => $case->value, Punctuality::cases())),
             ],
        ]);

        // Custom validation to ensure 'status' is 'delivered' when 'duration-minutes' is specified
        if ($request->filled('duration-minutes') && $request->input('status') !== CareVisitDeliveryStatus::Delivered->value) {
            $validator->after(function ($validator) {
                $validator->errors()->add('status', 'The status must be "' . CareVisitDeliveryStatus::Delivered->value . '" if "duration-minutes" is specified.');
            });
        }

        // Custom validation to ensure 'status' is 'delivered' or 'frustrated' when 'punctuality' is specified and equal to anything but missed
        $hasPunctualityValidStatus = in_array($request->input('status'), [CareVisitDeliveryStatus::Delivered->value, CareVisitDeliveryStatus::Frustrated->value]);
        if ($request->filled('punctuality') && $request->input('punctuality') !== Punctuality::Missed->value && !$hasPunctualityValidStatus) {
            $validator->after(function ($validator) {
                $validator->errors()->add('punctuality', 'The status must be "' . CareVisitDeliveryStatus::Delivered->value . '" or "' . CareVisitDeliveryStatus::Frustrated->value . '" if "punctuality" is specified.');
            });
        }

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        return $next($request);
    }
}
