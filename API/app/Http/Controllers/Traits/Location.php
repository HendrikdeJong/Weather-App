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
        $response = Http::withOptions(['verify_host' => false])->get("https://nominatim.openstreetmap.org/reverse", [
            'lat' => $lat,
            'lon' => $lon,
            'format' => 'json',
            'addressdetails' => 1,
        ]);

        if (!$response->successful()) {
            return null;
        }

        $address = $response->json()['address'] ?? [];

        // Try different levels of granularity
        return $address['city']
            ?? $address['town']
            ?? $address['village']
            ?? $address['hamlet']
            ?? $address['municipality']
            ?? null;
    }
}