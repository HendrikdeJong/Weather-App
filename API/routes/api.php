<?php

use App\Http\Controllers\ApiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/weather/forecast/daily', [ApiController::class, "GetWeatherForecastWeek"]);
Route::get('/weather/forecast/hourly', [ApiController::class, "GetWeatherForecastHour"]);
Route::get('/weather/current', [ApiController::class, "GetCurrentWeather"]);
Route::get('/weather', [ApiController::class, "GetCurrentWeather"]);
