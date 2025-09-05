<?php

namespace App\Http\Controllers\Traits;

use Illuminate\Http\JsonResponse;

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
     * Standardized error response, optionally including debug info
     */
    private function errorResponse(string $message = 'Unable to fetch weather data', array $debug = []): JsonResponse
    {
        $response = ['error' => $message];

        if (!empty($debug)) {
            $response['_debug'] = $debug;
        }

        return response()->json($response, 500);
    }
}
