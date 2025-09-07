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



}
