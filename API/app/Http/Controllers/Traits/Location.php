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
            'User-Agent' => 'MyWeatherApp/1.0 (myemail@example.com)', // required
            'Accept' => 'application/json', // 👈 force JSON response
        ])->withOptions(['verify_host' => false])->get("https://nominatim.openstreetmap.org/reverse", [
                    'lat' => $lat,
                    'lon' => $lon,
                    'format' => 'json',// required, else will return xml
                    'addressdetails' => 1,
                ]);


        if (!$response->successful()) {
            logger()->error('Nominatim error', ['status' => $response->status(), 'body' => $response->body()]);
            return null;
        }

        $address = $response->json()['address'] ?? [];

        $location = $address['city']
            ?? $address['town']
            ?? $address['village']
            ?? $address['hamlet']
            ?? $address['municipality']
            ?? $address['county']
            ?? $address['state']
            ?? null;

        return $location;
    }


}