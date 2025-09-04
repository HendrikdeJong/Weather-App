<?php

namespace App\Http\Controllers;


use App\Http\Controllers\Traits\Helpers;
use App\Http\Controllers\Traits\Location;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\DailyForecast;
use App\Models\HourlyForecast;

class ApiController extends Controller
{
    use Helpers;
    use Location;

    public function getCurrentWeather(Request $request)
    {
        $location = $this->getLocation($request->validate($this->validationRules()));

        $currentHour = now()->format('Y-m-d\TH:00:00\Z');

        if (!$weather) {
            return $this->errorResponse();
        }

        return response()->json($weather);
    }

    public function getWeatherForecastWeek(Request $request)
    {
        $location = $this->getLocation($request->validate($this->validationRules()));

        if (!$weather) {
            return $this->errorResponse();
        }

        return response()->json($weather);
    }

    public function getWeatherForecastHour(Request $request)
    {
        $location = $this->getLocation($request->validate($this->validationRules()));

        if (!$weather) {
            return $this->errorResponse();
        }

        return response()->json($weather);
    }






    private function fetchApiData(string $location): ?array
    {
        $apiKey = config('services.tomorrowio.key');

        $response = Http::withOptions(['verify_host' => false])->get(
            "https://api.tomorrow.io/v4/weather/forecast",
            [
                'location' => $location,
                'apikey' => $apiKey,
            ]
        );

        return $response->successful() ? $response->json() : null;
    }
}
