<?php

use App\Http\Controllers\ApiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/weather/forecast/daily', [ApiController::class, "getDailyForecast"]);
Route::get('/weather/forecast/hourly', [ApiController::class, "getHourlyForecast"]);
Route::get('/weather/current', [ApiController::class, "getCurrentWeather"]);
