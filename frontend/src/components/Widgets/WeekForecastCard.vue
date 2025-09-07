<template>
  <v-card
    class="pa-4 rounded-xl mt-4"
    :style="{ background: colors.backgroundCard, color: colors.textPrimary }"
  >
    <h3 class="text-subtitle-1 font-weight-medium mb-2">5-Day Forecast</h3>
    <v-row dense justify="space-around" class="flex-nowrap" style="overflow-x: auto">
      <v-col
        v-for="(day, idx) in week.slice(0, 5)"
        :key="day.date ?? idx"
        class="text-center"
        cols="auto"
      >
        <p class="text-caption" :style="{ color: colors.textSecondary }">
          {{ formatDay(day.date) }}
        </p>
        <WeatherIcon :code="day.weatherCodeMax" :time="'12:00'" :size="48" class="my-1" />
        <p class="text-body-2 font-weight-medium">
          {{ day.temperatureMax }}° / {{ day.temperatureMin }}°
        </p>
      </v-col>
    </v-row>
  </v-card>
</template>

<script setup lang="ts">
  import { computed } from 'vue'
  import { useAppStore } from '@/stores/app'
  import WeatherIcon from '../WeatherIcon.vue'
  import { useThemeColors } from '@/composables/useThemeColors'

  const store = useAppStore()
  const week = computed(() => store.WeekForecast || [])
  const { colors } = useThemeColors()

  const formatDay = (date: string | null) =>
    date ? new Date(date).toLocaleDateString([], { weekday: 'short' }) : ''
</script>
