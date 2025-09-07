<template>
  <v-card class="pa-4 rounded-xl">
    <div class="d-flex justify-space-between align-center">
      <div>
        <h2 class="text-h6 font-weight-bold">{{ current?.location }}</h2>
        <p class="text-caption text-grey">{{ current?.time }}</p>
      </div>
      <WeatherIcon :code="current?.weatherCode ?? null" :size="72" />
    </div>

    <div class="d-flex justify-space-between mt-4">
      <div>
        <p class="text-h3 font-weight-bold">{{ current?.temperature }}°</p>
        <p class="text-body-2 text-grey">Feels like {{ current?.temperatureApparent }}°</p>
      </div>
      <div class="text-body-2 text-right">
        <p>{{ current?.windSpeed }} km/h</p>
        <p>{{ current?.humidity }}%</p>
      </div>
    </div>
  </v-card>
</template>

<script lang="ts" setup>
  import { computed } from 'vue'
  import { useAppStore } from '@/stores/app'
  import WeatherIcon from '../WeatherIcon.vue'

  const store = useAppStore()
  const current = computed(() => store.currentWeather)
</script>
