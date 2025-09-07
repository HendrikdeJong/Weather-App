<template>
  <v-app>
    <router-view />
  </v-app>
</template>

<script lang="ts" setup>
  import { onMounted, onUnmounted } from 'vue'
  import { useLocationStore } from '@/stores/location'
  import { useAppStore } from '@/stores/app'

  const location = useLocationStore()
  const app = useAppStore()
  let intervalId: number | undefined
  async function loadWeather() {
    if (location.latitude !== null && location.longitude !== null) {
      await app.fetchWeather(undefined, location.latitude, location.longitude)
    }
  }

  // Watch for location changes and load weather immediately when available
  import { watch } from 'vue'

  watch(
    () => [location.latitude, location.longitude],
    (newVal, oldVal) => {
      const [lat, lon] = newVal
      const [prevLat, prevLon] = oldVal || []
      if (lat !== null && lon !== null && (lat !== prevLat || lon !== prevLon)) {
        loadWeather()
      }
    },
    { immediate: true }
  )

  onMounted(async () => {
    await location.fetchLocation()
    await loadWeather()

    // refresh every 10 minutes
    intervalId = window.setInterval(loadWeather, 10 * 60 * 1000)
  })

  onUnmounted(() => {
    if (intervalId) clearInterval(intervalId)
  })
</script>
