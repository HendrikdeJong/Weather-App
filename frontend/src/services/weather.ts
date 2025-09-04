// src/services/weather.ts
import api from './api'

export interface HourlyForecast {
  [key: string]: any
  time: string
  altimeterSetting: number | null
  cloudBase: number | null
  cloudCeiling: number | null
  cloudCover: number | null
  dewPoint: number | null
  evapotranspiration: number | null
  freezingRainIntensity: number | null
  humidity: number | null
  iceAccumulation: number | null
  iceAccumulationLwe: number | null
  precipitationProbability: number | null
  pressureSeaLevel: number | null
  pressureSurfaceLevel: number | null
  rainAccumulation: number | null
  rainIntensity: number | null
  sleetAccumulation: number | null
  sleetAccumulationLwe: number | null
  sleetIntensity: number | null
  snowAccumulation: number | null
  snowAccumulationLwe: number | null
  snowIntensity: number | null
  temperature: number | null
  temperatureApparent: number | null
  uvHealthConcern: number | null
  uvIndex: number | null
  visibility: number | null
  weatherCode: number | null
  windDirection: number | null
  windGust: number | null
  windSpeed: number | null
}

export interface DailyForecast {
  [key: string]: any
  time: string | null
  altimeterSettingAvg: number | null
  altimeterSettingMax: number | null
  altimeterSettingMin: number | null
  cloudBaseAvg: number | null
  cloudBaseMax: number | null
  cloudBaseMin: number | null
  cloudCeilingAvg: number | null
  cloudCeilingMax: number | null
  cloudCeilingMin: number | null
  cloudCoverAvg: number | null
  cloudCoverMax: number | null
  cloudCoverMin: number | null
  dewPointAvg: number | null
  dewPointMax: number | null
  dewPointMin: number | null
  evapotranspirationAvg: number | null
  evapotranspirationMax: number | null
  evapotranspirationMin: number | null
  evapotranspirationSum: number | null
  freezingRainIntensityAvg: number | null
  freezingRainIntensityMax: number | null
  freezingRainIntensityMin: number | null
  humidityAvg: number | null
  humidityMax: number | null
  humidityMin: number | null
  iceAccumulationAvg: number | null
  iceAccumulationLweAvg: number | null
  iceAccumulationLweMax: number | null
  iceAccumulationLweMin: number | null
  iceAccumulationLweSum: number | null
  iceAccumulationMax: number | null
  iceAccumulationMin: number | null
  iceAccumulationSum: number | null
  moonriseTime: string | null
  moonsetTime: string | null
  precipitationProbabilityAvg: number | null
  precipitationProbabilityMax: number | null
  precipitationProbabilityMin: number | null
  pressureSeaLevelAvg: number | null
  pressureSeaLevelMax: number | null
  pressureSeaLevelMin: number | null
  pressureSurfaceLevelAvg: number | null
  pressureSurfaceLevelMax: number | null
  pressureSurfaceLevelMin: number | null
  rainAccumulationAvg: number | null
  rainAccumulationMax: number | null
  rainAccumulationMin: number | null
  rainAccumulationSum: number | null
  rainIntensityAvg: number | null
  rainIntensityMax: number | null
  rainIntensityMin: number | null
  sleetAccumulationAvg: number | null
  sleetAccumulationLweAvg: number | null
  sleetAccumulationLweMax: number | null
  sleetAccumulationLweMin: number | null
  sleetAccumulationLweSum: number | null
  sleetAccumulationMax: number | null
  sleetAccumulationMin: number | null
  sleetIntensityAvg: number | null
  sleetIntensityMax: number | null
  sleetIntensityMin: number | null
  snowAccumulationAvg: number | null
  snowAccumulationLweAvg: number | null
  snowAccumulationLweMax: number | null
  snowAccumulationLweMin: number | null
  snowAccumulationLweSum: number | null
  snowAccumulationMax: number | null
  snowAccumulationMin: number | null
  snowAccumulationSum: number | null
  snowIntensityAvg: number | null
  snowIntensityMax: number | null
  snowIntensityMin: number | null
  sunriseTime: string | null
  sunsetTime: string | null
  temperatureApparentAvg: number | null
  temperatureApparentMax: number | null
  temperatureApparentMin: number | null
  temperatureAvg: number | null
  temperatureMax: number | null
  temperatureMin: number | null
  uvHealthConcernAvg: number | null
  uvHealthConcernMax: number | null
  uvHealthConcernMin: number | null
  uvIndexAvg: number | null
  uvIndexMax: number | null
  uvIndexMin: number | null
  visibilityAvg: number | null
  visibilityMax: number | null
  visibilityMin: number | null
  weatherCodeMax: number | null
  weatherCodeMin: number | null
  windDirectionAvg: number | null
  windGustAvg: number | null
  windGustMax: number | null
  windGustMin: number | null
  windSpeedAvg: number | null
  windSpeedMax: number | null
  windSpeedMin: number | null
}

export const WeatherService = {
  async getCurrentWeather(params: { city?: string; lat?: number; lon?: number }) {
    if (
      (params.city && (params.lat || params.lon)) ||
      (!params.city && (params.lat == null || params.lon == null))
    ) {
      throw new Error(
        'Provide either city or both lat and lon, not both or incomplete coordinates.'
      )
    }
    const { data } = await api.get<HourlyForecast>('/weather/current', {
      params,
    })
    return data
  },

  async getDailyForecast(params: { city?: string; lat?: number; lon?: number }) {
    if (
      (params.city && (params.lat || params.lon)) ||
      (!params.city && (params.lat == null || params.lon == null))
    ) {
      throw new Error(
        'Provide either city or both lat and lon, not both or incomplete coordinates.'
      )
    }
    const { data } = await api.get<DailyForecast[]>('/weather/forecast/weekly', {
      params,
    })
    return data
  },

  async getHourlyForecast(params: { city?: string; lat?: number; lon?: number }) {
    if (
      (params.city && (params.lat || params.lon)) ||
      (!params.city && (params.lat == null || params.lon == null))
    ) {
      throw new Error(
        'Provide either city or both lat and lon, not both or incomplete coordinates.'
      )
    }
    const { data } = await api.get<HourlyForecast[]>('/weather/forecast/hourly', {
      params,
    })
    return data
  },
}
