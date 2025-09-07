<template>
  <v-card
    class="pa-4 rounded-xl mt-4"
    :style="{ background: colors.backgroundCard, color: colors.textPrimary }"
  >
    <h3 class="text-subtitle-1 font-weight-medium mb-2">Hourly Forecast</h3>
    <v-slide-group show-arrows>
      <v-slide-group-item
        v-for="hour in hours"
        :key="hour.local_time ?? `hour-${hours.indexOf(hour)}`"
      >
        <div
          class="pa-3 text-center"
          elevation="1"
          rounded
          :style="{ background: colors.backgroundCard, color: colors.textPrimary }"
        >
          <p class="text-caption mb-1" :style="{ color: colors.textSecondary }">
            {{ formatHour(hour.local_time) }}
          </p>
          <WeatherIcon :code="hour.weatherCode" :time="hour.local_time ?? undefined" :size="48" />
          <p class="text-body-2 font-weight-medium mt-1">{{ hour.temperature }}°</p>
          <!-- would love to add graph point that links up to all other group items and will display temperature drops and peaks with its color changing when we also have light/sun/'warmth' -->
          <v-row class="align-center justify-center mt-1" style="gap: 4px">
            <v-icon size="18" color="blue lighten-2">mdi-water</v-icon>
            <span class="text-body-2 font-weight-medium">
              {{ Math.round(hour.rainIntensity ?? 0) }}%
            </span>
          </v-row>
        </div>
      </v-slide-group-item>
    </v-slide-group>
  </v-card>
</template>

<script setup lang="ts">
  import { computed } from 'vue'
  import { useAppStore } from '@/stores/app'
  import WeatherIcon from '../WeatherIcon.vue'
  import { useThemeColors } from '@/composables/useThemeColors'

  const store = useAppStore()
  const hours = computed(() => store.DayForecast || [])
  const { colors } = useThemeColors()

  const formatHour = (time: string | null) => (time ? time.slice(0, 5) : '')
</script>
