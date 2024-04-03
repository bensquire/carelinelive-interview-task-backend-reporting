<?php

use App\Http\Controllers\CareVisitController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/total-visits', [CareVisitController::class, 'totalVisits'])
    ->middleware(\App\Http\Middleware\CareVisit\ValidateTotalVisits::class);

Route::get('/average-duration', [CareVisitController::class, 'averageDuration']);

Route::get('/punctuality', [CareVisitController::class, 'punctuality']);
