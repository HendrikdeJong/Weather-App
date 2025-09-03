<?php

namespace App\Http\Controllers;

use App\Models\Weather;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class ApiController extends Controller
{
    public function getWeatherforecast(Request $request)
    {
        $validated = $request->validate([
            'lat'  => 'required_without:city|numeric',
            'lon'  => 'required_without:city|numeric',
            'city' => 'required_without_all:lat,lon|string',
        ]);

        try {
            // Debug: log incoming request
            Log::debug("Incoming weather request", $validated);

            // Determine location key
            if (isset($validated['lat']) && isset($validated['lon'])) {
                $lat = $validated['lat'];
                $lon = $validated['lon'];
                $queryLocation = "{$lat},{$lon}";
                $location = $this->getlocation($lat, $lon);
                Log::debug("Resolved coordinates to location", $location);
            } else {
                $queryLocation = $validated['city'];
                $location = $queryLocation;
                Log::debug("Using city name for query", ['city' => $queryLocation]);
            }

            $locationKey = is_array($location) ? "{$location['lat']},{$location['lon']}" : $location;
            Log::debug("Final locationKey", ['locationKey' => $locationKey]);

            // Check DB cache
            $weather = Weather::where('location', $locationKey)->latest()->first();

            if ($weather && $weather->created_at->gt(now()->subHour())) {
                Log::info("Serving weather data from cache", ['location' => $locationKey]);
                return response()->json([
                    'source' => 'cache',
                    'data'   => json_decode($weather->data, true),
                ]);
            }

            // Fetch new weather data
            $apiData = $this->fetchNewWeatherData($locationKey);

            if (!$apiData) {
                Log::error("Weather API returned no data", ['location' => $locationKey]);
                return response()->json(['error' => 'Unable to fetch weather data'], 500);
            }

            // Save or update DB
            Weather::updateOrCreate(
                ['location' => $locationKey],
                [
                    'data'       => json_encode($apiData),
                    'updated_at' => now(),
                ]
            );

            Log::info("Weather data saved to DB", ['location' => $locationKey]);

            return response()->json([
                'source' => 'api',
                'data'   => $apiData,
            ]);

        } catch (Exception $e) {
            Log::error("Weather API error: " . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'error' => 'Something went wrong while fetching the weather forecast.'
            ], 500);
        }
    }

    private function fetchNewWeatherData(string $locationKey): ?array
    {
        try {
           $response = Http::timeout(10)
            ->withOptions(['verify' => false])
            ->get("https://api.tomorrow.io/v4/weather/forecast", [
                'location' => $locationKey,
                'apikey'   => env("API_KEY"),
            ]);

            if ($response->failed()) {
                // log + return the real error
                Log::error("Tomorrow.io API failed", [
                    'status' => $response->status(),
                    'body'   => $response->body(),
                ]);

                return [
                    'error'  => 'Tomorrow.io API error',
                    'status' => $response->status(),
                    'body'   => $response->json(),
                ];
            }

            return $response->json();

        } catch (Exception $e) {
            Log::error("Tomorrow.io API exception: " . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return [
                'error' => 'Exception while calling Tomorrow.io',
                'message' => $e->getMessage()
            ];
        }
    }


    private function getlocation(string $lat, string $lon): array
    {
        try {
            Log::debug("Resolving location with OpenStreetMap", ['lat' => $lat, 'lon' => $lon]);

            $response = Http::timeout(10)->get("https://nominatim.openstreetmap.org/reverse", [
                'format' => 'jsonv2',
                'lat'    => $lat,
                'lon'    => $lon,
            ]);

            if ($response->failed()) {
                Log::warning("OpenStreetMap API failed", [
                    'status' => $response->status(),
                    'body'   => $response->body()
                ]);
                return ['lat' => $lat, 'lon' => $lon];
            }

            $data = $response->json();
            Log::debug("OpenStreetMap API response", $data);

            return [
                'lat'          => $data['lat'] ?? $lat,
                'lon'          => $data['lon'] ?? $lon,
                'town'         => $data['address']['town'] ?? null,
                'state'        => $data['address']['state'] ?? null,
                'village'      => $data['address']['village'] ?? null,
                'country'      => $data['address']['country'] ?? null,
                'municipality' => $data['address']['municipality'] ?? null,
            ];
        } catch (Exception $e) {
            Log::error("OpenStreetMap API exception: " . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return ['lat' => $lat, 'lon' => $lon];
        }
    }
}
