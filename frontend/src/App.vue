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
