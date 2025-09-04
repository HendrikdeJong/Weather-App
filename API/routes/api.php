<?php

use App\Http\Controllers\ApiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/weather/forecast', [ApiController::class, "GetWeatherForecast"]);
Route::get('/weather', [ApiController::class, "GetCurrentWeather"]);
