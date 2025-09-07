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
     * Get current weather for a location
     */
    public function getCurrentWeather(Request $request)
    {

        $location = $this->getLocation($request->validate($this->validationRules()));
        if (!$location) {
            return response()->json(['error' => 'Invalid location'], 422);
        }
        // return response()->json($location, 422);

        $weather = HourlyForecast::where('location', $location)
            ->where('date', now()->toDateString())
            ->orderBy('time')
            ->first();

        if (!$weather) {
            $weather = $this->updateHourlyForecast($location)->first();
        }

        return $weather
            ? response()->json($weather)
            : response()->json(['error' => 'Could not retrieve current weather data', 'weather' => $weather], 404);

        // return $weather
        //     ? response()->json($weather)
        //     : response()->json(['error' => 'Could not retrieve current weather data'], 404);
    }

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
            ->orderBy('time')
            ->take(5)
            ->get();

        if ($weather->isEmpty()) {
            $weather = $this->updateDailyForecast($location);
        }

        return $weather->isEmpty()
            ? response()->json(['error' => 'could not retrieve daily forecast data'], 404)
            : response()->json($weather);
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
            ? response()->json(['error' => 'could not retrieve hourly forecast data'], 404)
            : response()->json($weather);
    }

    /**
     * Fetch and store daily forecasts
     */
    private function updateDailyForecast(string $location): Collection
    {
        $apiData = $this->fetchApiData($location);

        if (!$apiData || empty($apiData['daily'])) {
            return collect();
        }

        // Extract dates we’re about to insert
        $dates = collect($apiData['daily'])->pluck('date')->unique();

        // Delete existing forecasts for those dates
        DailyForecast::where('location', $location)
            ->whereIn('date', $dates)
            ->delete();

        // Insert new ones
        foreach ($apiData['daily'] as $forecast) {
            DailyForecast::create([
                'location' => $location,
                'date' => $forecast['date'],
                'time' => $forecast['time'] ?? '00:00',
                // merge values dynamically
                ...$forecast,
            ]);
        }

        // Always return latest 5 days from today
        return DailyForecast::where('location', $location)
            ->where('date', '>=', now()->toDateString())
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

        if (!$apiData || empty($apiData['hourly'])) {
            return collect();
        }

        // Extract dates & times
        $deleteConditions = collect($apiData['hourly'])->map(fn($f) => [
            'date' => $f['date'],
            'time' => $f['time'],
        ]);

        // Delete only matching rows (date + time)
        foreach ($deleteConditions as $cond) {
            HourlyForecast::where('location', $location)
                ->where('date', $cond['date'])
                ->where('time', $cond['time'])
                ->delete();
        }

        // Insert new forecasts
        foreach ($apiData['hourly'] as $forecast) {
            HourlyForecast::create([
                'location' => $location,
                'date' => $forecast['date'],
                'time' => $forecast['time'],
                ...$forecast,
            ]);
        }

        // Return upcoming 24 hours from "now"
        return HourlyForecast::where('location', $location)
            ->where(function ($q) {
                $q->where('date', '>', now()->toDateString())
                    ->orWhere(function ($q2) {
                        $q2->where('date', now()->toDateString())
                            ->where('time', '>=', now()->format('H:i'));
                    });
            })
            ->orderBy('date')
            ->orderBy('time')
            ->take(24)
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
