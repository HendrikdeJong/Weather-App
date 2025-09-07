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

        // Fetch timezone for this location from last stored forecast
        $timezone = HourlyForecast::where('location', $location)
            ->orderByDesc('local_datetime')
            ->value('timezone') ?? 'UTC';

        // Current datetime at forecast location
        $locationNow = \Carbon\Carbon::now($timezone);
        $localDate = $locationNow->toDateString();
        $localTime = $locationNow->format('H:i');

        $weather = HourlyForecast::where('location', $location)
            ->where('local_date', $localDate)
            ->where('local_time', '>=', $localTime)
            ->orderBy('local_time')
            ->first();

        if (!$weather) {
            $weather = $this->updateHourlyForecast($location)->first();
        }

        return $weather
            ? response()->json($weather)
            : response()->json(['error' => 'Could not retrieve current weather data', 'weather' => $weather], 404);
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

        $timezone = HourlyForecast::where('location', $location)
            ->orderByDesc('local_datetime')
            ->value('timezone') ?? 'UTC';

        $locationNow = \Carbon\Carbon::now($timezone);
        $localDate = $locationNow->toDateString();
        $localTime = $locationNow->format('H:i');

        $weather = HourlyForecast::where('location', $location)
            ->where(function ($q) use ($localDate, $localTime) {
                $q->where('local_date', '>', $localDate)
                    ->orWhere(function ($q2) use ($localDate, $localTime) {
                        $q2->where('local_date', $localDate)
                            ->where('local_time', '>=', $localTime);
                    });
            })
            ->orderBy('local_date')
            ->orderBy('local_time')
            ->take(23)
            ->get();

        if ($weather->isEmpty()) {
            $weather = $this->updateHourlyForecast($location)->take(5);
        }

        return $weather->isEmpty()
            ? response()->json(['error' => 'could not retrieve hourly forecast data'], 404)
            : response()->json($weather);
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

        $timezone = DailyForecast::where('location', $location)
            ->orderByDesc('local_datetime')
            ->value('timezone') ?? 'UTC';

        $locationNow = \Carbon\Carbon::now($timezone);
        $localDate = $locationNow->toDateString();

        $weather = DailyForecast::where('location', $location)
            ->where('local_date', '>=', $localDate)
            ->orderBy('local_date')
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
                'local_datetime' => $forecast['local_datetime'],
                'local_date' => $forecast['local_date'],
                'local_time' => $forecast['local_time'],
                'timezone' => $forecast['timezone'],
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
                'local_datetime' => $forecast['local_datetime'],
                'local_date' => $forecast['local_date'],
                'local_time' => $forecast['local_time'],
                'timezone' => $forecast['timezone'],
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

        // Get coords from response
        $lat = $data['location']['lat'] ?? null;
        $lon = $data['location']['lon'] ?? null;

        $timezone = $lat && $lon ? $this->getTimezoneOffset($lat, $lon) : 'UTC';

        $flatten = fn($timelines) => collect($timelines ?? [])->map(function ($item) use ($timezone) {
            $isoTime = $item['time']; // UTC timestamp
            $date = substr($isoTime, 0, 10);
            $time = substr($isoTime, 11, 5);

            // Convert UTC → Local
            $utc = \Carbon\Carbon::parse($isoTime)->setTimezone('UTC');
            $local = $timezone ? $utc->copy()->setTimezone($timezone) : $utc;

            return array_merge(
                [
                    'datetime' => $isoTime, // original UTC
                    'date' => $date,
                    'time' => $time,
                    'local_datetime' => $local->toIso8601String(),
                    'local_date' => $local->toDateString(),
                    'local_time' => $local->format('H:i'),
                    'timezone' => $timezone,
                ],
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
