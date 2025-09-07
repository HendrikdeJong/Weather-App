// src/stores/app.ts
import { defineStore } from 'pinia'
import { WeatherService } from '@/services/weather'
import type { HourlyForecast, DailyForecast } from '@/services/weather'

export const useAppStore = defineStore('app', {
  state: () => ({
    currentWeather: null as HourlyForecast | null,
    DayForecast: [] as HourlyForecast[],
    WeekForecast: [] as DailyForecast[],
    loading: false,
    error: null as string | null,
  }),

  actions: {
    async fetchWeather(city?: string, lat?: number, lon?: number) {
      this.loading = true
      this.error = null
      try {
        const params: { city?: string; lat?: number; lon?: number } = {}
        if (city) {
          params.city = city
        } else if (lat != null && lon != null) {
          params.lat = lat
          params.lon = lon
        }

        this.currentWeather = await WeatherService.getCurrentWeather(params)
        this.DayForecast = await WeatherService.getHourlyForecast(params)
        this.WeekForecast = await WeatherService.getDailyForecast(params)
      } catch (err: any) {
        this.error = err.message || 'Failed to fetch weather'
      } finally {
        this.loading = false
      }
    },
  },
})
