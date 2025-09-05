<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Traits\Helpers;
use App\Http\Controllers\Traits\Location;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\DailyForecast;
use App\Models\HourlyForecast;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class ApiController extends Controller
{
    use Helpers, Location;

    /**
     * Get 5-day daily forecast for a location
     */
    public function getDailyForecast(Request $request)
    {

        $location = $this->getLocation($request->validate($this->validationRules()));
        if (!$location) {
            return response()->json(['error' => 'Invalid location'], 422);
        }

        $weather = DailyForecast::where('location', $location)
            ->where('date', now()->toDateString())
            ->take(5)
            ->get();

        if ($weather->isEmpty()) {
            $weather = $this->updateDailyForecast($location);
        }

        return $weather->isEmpty()
            ? $this->errorResponse()
            : response()->json($weather);
    }

    /**
     * Get current weather for a location
     */
    public function getCurrentWeather(Request $request)
    {

        $location = $this->getLocation($request->validate($this->validationRules()));
        if (!$location) {
            return response()->json(['error' => 'Invalid location'], 422);
        }

        $weather = HourlyForecast::where('location', $location)
            ->where('date', now()->toDateString())
            ->orderBy('time')
            ->first();

        if (!$weather) {
            $weather = $this->updateHourlyForecast($location)->first();
        }

        return $weather
            ? response()->json($weather)
            : $this->errorResponse();
    }

    /**
     * Get next 5-hour hourly forecast for a location
     */
    public function getHourlyForecast(Request $request)
    {

        $location = $this->getLocation($request->validate($this->validationRules()));
        if (!$location) {
            return response()->json(['error' => 'Invalid location'], 422);
        }

        $weather = HourlyForecast::where('location', $location)
            ->where('date', now()->toDateString())
            ->orderBy('time')
            ->take(5)
            ->get();

        if ($weather->isEmpty()) {
            $weather = $this->updateHourlyForecast($location)->take(5);
        }

        return $weather->isEmpty()
            ? $this->errorResponse()
            : response()->json($weather);
    }

    /**
     * Fetch and store daily forecasts
     */
    private function updateDailyForecast(string $location): Collection
    {
        $apiData = $this->fetchApiData($location);

        if (!$apiData || !isset($apiData['daily']) || !is_array($apiData['daily'])) {
            return collect();
        }

        DailyForecast::where('location', $location)
            ->whereIn('date', collect($apiData['daily'])->pluck('date'))
            ->delete();

        foreach ($apiData['daily'] as $forecast) {
            DailyForecast::create(array_merge(
                ['location' => $location],
                $forecast
            ));
        }

        return DailyForecast::where('location', $location)
            ->orderBy('date')
            ->take(5)
            ->get();
    }

    /**
     * Fetch and store hourly forecasts
     */
    private function updateHourlyForecast(string $location): Collection
    {
        $apiData = $this->fetchApiData($location);

        if (!$apiData || !isset($apiData['hourly']) || !is_array($apiData['hourly'])) {
            return collect();
        }

        HourlyForecast::where('location', $location)
            ->whereIn('date', collect($apiData['hourly'])->pluck('date'))
            ->delete();

        foreach ($apiData['hourly'] as $forecast) {
            HourlyForecast::create(array_merge(
                ['location' => $location],
                $forecast
            ));
        }

        return HourlyForecast::where('location', $location)
            ->where('date', now()->toDateString())
            ->orderBy('time')
            ->get();

    }

    /**
     * Call Tomorrow.io API (more debug-friendly)
     *
     * If $debug === true the method returns an array with a "_debug" key on failure,
     * allowing you to call endpoints with ?debug=1 and receive debug info in the HTTP response.
     */
    private function fetchApiData(string $location): ?array
    {
        $apiKey = config('services.tomorrowio.key');

        $response = Http::withOptions(['verify_host' => false])
            ->get("https://api.tomorrow.io/v4/weather/forecast", [
                'location' => $location,
                'apikey' => $apiKey,
            ]);

        if (!$response->successful()) {
            \Log::channel('stderr')->error('Tomorrow.io API error', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            return null;
        }

        $data = $response->json();

        $flatten = fn($timelines) => collect($timelines ?? [])->map(function ($item) {
            $date = substr($item['time'], 0, 10);
            $time = substr($item['time'], 11, 5);

            return array_merge(
                ['date' => $date, 'time' => $time],
                $item['values'] ?? []
            );
        })->all();

        return [
            'daily' => $flatten($data['timelines']['daily'] ?? []),
            'hourly' => $flatten($data['timelines']['hourly'] ?? []),
            'minutely' => $flatten($data['timelines']['minutely'] ?? []),
        ];
    }
}
