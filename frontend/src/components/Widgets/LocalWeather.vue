<template>
  <div class="space-y-4">
    <CurrentWeather />
    <DailyForecast />
    <HourlyForecast />
  </div>
</template>

<script lang="ts" setup>
  import CurrentWeather from './CurrentWeather.vue'
  import DailyForecast from './DailyForecast.vue'
  import HourlyForecast from './HourlyForecast.vue'
</script>
