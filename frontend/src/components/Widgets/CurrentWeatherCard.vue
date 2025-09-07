<template>
  <v-card
    class="pa-6 rounded-3xl d-flex flex-column align-center justify-center"
    :style="{ background: currentBackground, color: colors.textPrimary }"
  >
    <div class="text-center">
      <p class="text-h2 font-weight-bold">{{ current?.temperature }}°</p>
      <p class="text-body-1">Feels like {{ current?.temperatureApparent }}°</p>
      <p class="text-body-2 mt-1"><v-icon small>mdi-map-marker</v-icon> {{ current?.location }}</p>
    </div>

    <WeatherIcon
      :code="current?.weatherCode ?? null"
      :time="current?.local_time ?? undefined"
      :size="120"
      class="my-4"
    />

    <v-row class="mt-4" justify="space-around">
      <v-col class="text-center">
        <v-icon>mdi-weather-windy</v-icon>
        <p class="text-caption">Wind</p>
        <p class="text-body-2">{{ current?.windSpeed }} km/h</p>
      </v-col>
      <v-col class="text-center">
        <v-icon>mdi-water-percent</v-icon>
        <p class="text-caption">Humidity</p>
        <p class="text-body-2">{{ current?.humidity }}%</p>
      </v-col>
      <v-col class="text-center">
        <v-icon>mdi-weather-sunset-up</v-icon>
        <p class="text-caption">Sunrise</p>
        <p class="text-body-2">
          {{
            day?.sunriseTime
              ? new Date(day.sunriseTime).toLocaleTimeString([], {
                  hour: '2-digit',
                  minute: '2-digit',
                })
              : '-'
          }}
        </p>
      </v-col>
      <v-col class="text-center">
        <v-icon>mdi-weather-sunset-down</v-icon>
        <p class="text-caption">Sunset</p>
        <p class="text-body-2">
          {{
            day?.sunsetTime
              ? new Date(day.sunsetTime).toLocaleTimeString([], {
                  hour: '2-digit',
                  minute: '2-digit',
                })
              : '-'
          }}
        </p>
      </v-col>
    </v-row>
  </v-card>
</template>

<script setup lang="ts">
  import { computed } from 'vue'
  import WeatherIcon from '../WeatherIcon.vue'
  import { useAppStore } from '@/stores/app'
  import type { DailyForecast } from '@/services/weather'
  import { useThemeColors } from '@/composables/useThemeColors'

  const store = useAppStore()
  const current = computed(() => store.currentWeather)
  const day = computed<DailyForecast>(() => store.WeekForecast[0] || null)
  const { colors } = useThemeColors()

  const currentBackground = computed(() => {
    if (!current.value || !current.value.local_time) return '#00008c'
    const hour = new Date(current.value.local_time).getHours()
    return hour >= 6 && hour <= 18
      ? 'linear-gradient(to bottom, #4facfe, #00f2fe)'
      : 'linear-gradient(to bottom, #1c1c1c, #00008c)'
  })
</script>
