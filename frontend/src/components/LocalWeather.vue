<template>
  <div class="space-y-4 w-1/2 mx-auto">
    <CurrentWeather />
    <DailyForecast />
    <HourlyForecast />
  </div>
</template>

<script lang="ts" setup>
  import CurrentWeather from './Widgets/CurrentWeather.vue'
  import DailyForecast from './Widgets/DailyForecast.vue'
  import HourlyForecast from './Widgets/HourlyForecast.vue'
</script>
