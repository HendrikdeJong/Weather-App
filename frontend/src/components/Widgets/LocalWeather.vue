<template>
  <v-card class="pa-2" elevation="1" variant="tonal" style="max-width: 500px">
    <template v-if="location.loading">
      <v-skeleton-loader type="card" />
    </template>

    <template v-else-if="location.error">
      <v-alert type="error" variant="tonal">{{ location.error }}</v-alert>
    </template>

    <template v-else-if="weatherStore.loading">
      <v-skeleton-loader type="card" />
    </template>

    <template v-else-if="weatherStore.error">
      <v-alert type="error" variant="tonal">{{ weatherStore.error }}</v-alert>
    </template>

    <template v-else-if="weatherStore.currentWeather">
      <div>
        <v-card-title primary-title>
          {{ weatherStore.currentWeather.location }}
        </v-card-title>

        <p><strong>Temperature:</strong> {{ weatherStore.currentWeather.temperature }}°C</p>
        <p><strong>Humidity:</strong> {{ weatherStore.currentWeather.humidity }}%</p>
      </div>
    </template>

    <template v-else>
      <v-alert type="info" variant="tonal">No weather data available</v-alert>
    </template>
  </v-card>
</template>

<script lang="ts" setup>
  import { watch } from 'vue'
  import { useLocationStore } from '@/stores/location'
  import { useAppStore } from '@/stores/app'

  const location = useLocationStore()
  const weatherStore = useAppStore()

  // when location changes, fetch weather
  watch(
    () => [location.latitude, location.longitude],
    async ([lat, lon]) => {
      if (lat && lon) {
        await weatherStore.fetchWeather(`${lat},${lon}`)
      }
    },
    { immediate: true }
  )
</script>
