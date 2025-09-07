<?php

namespace App\Http\Controllers\Traits;

use Illuminate\Support\Facades\Http;

trait Location
{
    private function getLocation(array $validated): ?string
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
        $response = Http::withHeaders([
            'User-Agent' => 'MyWeatherApp/1.0 (myemail@example.com)',
            'Accept' => 'application/json',
        ])->withOptions(['verify_host' => false])->get("https://nominatim.openstreetmap.org/reverse", [
                    'lat' => $lat,
                    'lon' => $lon,
                    'format' => 'json',
                    'addressdetails' => 1,
                ]);

        if (!$response->successful()) {
            abort(404, $response->body());
        }

        $address = $response->json()['address'] ?? [];

        // Only use town or village, fallback to other fields if both missing
        $location =
            $address['village']
            ?? $address['town']
            ?? $address['city']
            ?? $address['hamlet']
            ?? $address['municipality']
            ?? $address['county']
            ?? $address['state']
            ?? null;

        return $location;
    }


}