<template>
  <v-card class="pa-4 rounded-xl">
    <h3 class="text-subtitle-1 font-weight-medium mb-2">5-Day Forecast</h3>
    <v-row dense>
      <v-col v-for="day in week" :key="day.date" cols="2" class="text-center">
        <p class="text-caption">{{ formatDay(day.date) }}</p>
        <WeatherIcon :code="day.weatherCodeMax" :size="48" />
        <p class="text-body-2 font-weight-medium">
          {{ day.temperatureMax }}° / {{ day.temperatureMin }}°
        </p>
      </v-col>
    </v-row>
  </v-card>
</template>

<script lang="ts" setup>
  import { computed } from 'vue'
  import { useAppStore } from '@/stores/app'
  import type { DailyForecast } from '@/services/weather'
  import WeatherIcon from '../WeatherIcon.vue'

  const store = useAppStore()
  const week = computed<DailyForecast[]>(() => store.WeekForecast || [])

  const formatDay = (date: string) => new Date(date).toLocaleDateString([], { weekday: 'short' })
</script>
