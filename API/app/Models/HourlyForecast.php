<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HourlyForecast extends Model
{
    protected $table = "hourly_forecasts";
    protected $fillable = [
        "location",
        "time",
        "altimeterSetting",
        "cloudBase",
        "cloudCeiling",
        "cloudCover",
        "dewPoint",
        "evapotranspiration",
        "freezingRainIntensity",
        "humidity",
        "iceAccumulation",
        "iceAccumulationLwe",
        "precipitationProbability",
        "pressureSeaLevel",
        "pressureSurfaceLevel",
        "rainAccumulation",
        "rainIntensity",
        "sleetAccumulation",
        "sleetAccumulationLwe",
        "sleetIntensity",
        "snowAccumulation",
        "snowAccumulationLwe",
        "snowDepth",
        "snowIntensity",
        "temperature",
        "temperatureApparent",
        "uvHealthConcern",
        "uvIndex",
        "visibility",
        "weatherCode",
        "windDirection",
        "windGust",
        "windSpeed",
    ];
}
