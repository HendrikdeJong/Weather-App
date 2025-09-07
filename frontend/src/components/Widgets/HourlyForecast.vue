<template>
  <v-card class="pa-4 rounded-xl">
    <h3 class="text-subtitle-1 font-weight-medium mb-2">Next Hours</h3>
    <v-slide-group show-arrows>
      <v-slide-item v-for="hour in hours" :key="hour.time">
        <v-card class="ma-2 pa-2 text-center" elevation="1" rounded>
          <p class="text-caption">{{ formatHour(hour.time) }}</p>
          <WeatherIcon :code="hour.weatherCode" :size="40" />
          <p class="text-body-2 font-weight-medium">{{ hour.temperature }}°</p>
        </v-card>
      </v-slide-item>
    </v-slide-group>
  </v-card>
</template>

<script lang="ts" setup>
  import { computed } from 'vue'
  import { useAppStore } from '@/stores/app'
  import type { HourlyForecast } from '@/services/weather'
  import WeatherIcon from '../WeatherIcon.vue'

  const store = useAppStore()
  const hours = computed<HourlyForecast[]>(() => store.DayForecast || [])

  const formatHour = (time: string) => time?.slice(0, 5)
</script>
