<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\DailyForecast;
use App\Models\HourlyForecast;

class ApiController extends Controller
{
    public function getCurrentWeather(Request $request)
    {
        $location = $this->resolveLocation($request->validate($this->validationRules()));

        $weather = $this->getCachedWeather(HourlyForecast::class, $location);

        if (!$weather) {
            $weather = $this->fetchAndCacheWeather(HourlyForecast::class, $location);
            if (!$weather) {
                return $this->errorResponse();
            }
        }

        return response()->json($weather);
    }

    public function getWeatherForecast(Request $request)
    {
        $location = $this->resolveLocation($request->validate($this->validationRules()));

        $weather = $this->getCachedWeather(DailyForecast::class, $location, true);

        if (!$weather) {
            $weather = $this->fetchAndCacheWeather(DailyForecast::class, $location);
            if (!$weather) {
                return $this->errorResponse();
            }
        }

        return response()->json($weather);
    }

    /* ----------------- Helpers ----------------- */

    private function validationRules(): array
    {
        return [
            'lat' => 'required_without:city|numeric',
            'lon' => 'required_without:city|numeric',
            'city' => 'required_without_all:lat,lon|string',
        ];
    }

    private function getCachedWeather(string $model, string $location, bool $expireHourly = false)
    {
        $query = $model::where("location", $location);

        if ($expireHourly) {
            $query->where(function ($q) {
                $q->whereNull('created_at')
                    ->orWhere('created_at', '>=', now()->subHour());
            });
        }

        $weather = $query->first();

        // If exists but expired, ignore it
        if ($expireHourly && $weather?->created_at < now()->subHour()) {
            return null;
        }

        return $weather;
    }

    private function fetchAndCacheWeather(string $model, string $location)
    {
        $data = $this->fetchApiData($location);

        if (!$data) {
            return null;
        }

        $forecasts = match ($model) {
            HourlyForecast::class => $this->mapHourlyForecasts($data, $location),
            DailyForecast::class => $this->mapDailyForecasts($data, $location),
        };

        if (!empty($forecasts)) {
            foreach ($forecasts as $forecast) {
                $model::firstOrCreate(
                    [
                        'location' => $forecast['location'],
                        'time' => $forecast['time'], // unique per hour/day
                    ],
                    $forecast
                );
            }
        }

        return $forecasts;
    }


    private function fetchApiData(string $location): ?array
    {
        $apiKey = config('services.tomorrowio.key');

        $response = Http::withOptions(['verify_host' => false])->get("https://api.tomorrow.io/v4/weather/forecast", [
            'location' => $location,
            'apikey' => $apiKey,
        ]);

        return $response->successful() ? $response->json() : null;
    }

    private function mapHourlyForecasts(array $data, string $location): array
    {
        $results = [];

        foreach ($data['timelines']['hourly'] ?? [] as $hourly) {
            if (!isset($hourly['time'])) {
                continue;
            }

            $attributes = [
                'location' => $location,
                'time' => $hourly['time'], // already an ISO datetime string
            ];

            if (isset($hourly['values']) && is_array($hourly['values'])) {
                $filteredValues = array_diff_key($hourly['values'], array_flip(['location', 'time']));
                $attributes = array_merge($attributes, $filteredValues);
            }

            $results[] = $attributes;
        }

        return $results;
    }


    private function mapDailyForecasts(array $data, string $location): array
    {
        $results = [];

        foreach ($data['timelines']['daily'] ?? [] as $daily) {
            if (!isset($daily['time'])) {
                continue;
            }

            $attributes = [
                'location' => $location,
                'time' => $daily['time'], // use "time", not "date" to match fillable
            ];

            if (isset($daily['values']) && is_array($daily['values'])) {
                $filteredValues = array_diff_key($daily['values'], array_flip(['location', 'time']));
                $attributes = array_merge($attributes, $filteredValues);
            }

            $results[] = $attributes;
        }

        return $results;
    }


    private function resolveLocation(array $validated): ?string
    {
        if (isset($validated['city'])) {
            return strval($validated['city']);
        }

        if (isset($validated['lat'], $validated['lon'])) {
            return $this->fetchLatLonLocation($validated['lat'], $validated['lon']);
        }

        return null;
    }

    private function fetchLatLonLocation(float $lat, float $lon): ?string
    {
        $response = Http::withOptions(['verify_host' => false,])->get("https://nominatim.openstreetmap.org/reverse", [
            'lat' => $lat,
            'lon' => $lon,
        ]);

        return $response->successful()
            ? $response->json()['address']['city'] ?? null
            : null;
    }

    private function errorResponse()
    {
        return response()->json(['error' => 'Unable to fetch weather data'], 500);
    }
}
