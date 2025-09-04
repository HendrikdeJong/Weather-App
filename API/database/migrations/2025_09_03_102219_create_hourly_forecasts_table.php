<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('hourly_forecasts', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string("location");
            $table->date("time")->nullable();
            $table->float("altimeterSetting")->nullable();
            $table->float("cloudBase")->nullable();
            $table->float("cloudCeiling")->nullable();
            $table->float("cloudCover")->nullable();
            $table->float("dewPoint")->nullable();
            $table->float("evapotranspiration")->nullable();
            $table->float("freezingRainIntensity")->nullable();
            $table->float("humidity")->nullable();
            $table->float("iceAccumulation")->nullable();
            $table->float("iceAccumulationLwe")->nullable();
            $table->float("precipitationProbability")->nullable();
            $table->float("pressureSeaLevel")->nullable();
            $table->float("pressureSurfaceLevel")->nullable();
            $table->float("rainAccumulation")->nullable();
            $table->float("rainIntensity")->nullable();
            $table->float("sleetAccumulation")->nullable();
            $table->float("sleetAccumulationLwe")->nullable();
            $table->float("sleetIntensity")->nullable();
            $table->float("snowAccumulation")->nullable();
            $table->float("snowAccumulationLwe")->nullable();
            $table->float("snowDepth")->nullable();
            $table->float("snowIntensity")->nullable();
            $table->float("temperature")->nullable();
            $table->float("temperatureApparent")->nullable();
            $table->float("uvHealthConcern")->nullable();
            $table->float("uvIndex")->nullable();
            $table->float("visibility")->nullable();
            $table->float("weatherCode")->nullable();
            $table->float("windDirection")->nullable();
            $table->float("windGust")->nullable();
            $table->float("windSpeed")->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hourly_forecasts');
    }
};
