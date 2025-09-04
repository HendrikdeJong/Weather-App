<?php

namespace App\Http\Controllers\Traits;

use Illuminate\Support\Facades\Http;

trait Helpers
{
    /**
     * validation rules for location input
     * @return array{city: string, lat: string, lon: string}
     */
    private function validationRules(): array
    {
        return [
            'lat' => 'required_without:city|numeric',
            'lon' => 'required_without:city|numeric',
            'city' => 'required_without_all:lat,lon|string',
        ];
    }

    /**
     * will return a standardized error response
     * @return \Illuminate\Http\JsonResponse
     */
    private function errorResponse()
    {
        return response()->json(['error' => 'Unable to fetch weather data'], 500);
    }

    /**
     * Delete old weather forecasts from the database
     *
     * @param string $model Model class (HourlyForecast::class or DailyForecast::class)
     * @param \DateTime|string $olderThan Delete entries older than this threshold
     */
    private function deleteOldForecasts(string $model, $olderThan)
    {
        $threshold = $olderThan instanceof \DateTime ? $olderThan : now()->subDays($olderThan);

        $model::where('created_at', '<', $threshold)->delete();
    }

}
