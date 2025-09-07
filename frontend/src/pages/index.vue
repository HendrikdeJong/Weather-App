<template>
  <v-container fluid class="pa-4">
    <!-- Current Weather -->
    <current-weather-card class="mb-6" />

    <!-- Hourly Forecast -->
    <day-forecast-card class="mb-6" />

    <!-- 5-Day Forecast -->
    <week-forecast-card class="mb-6" />

    <!-- Sunrise / Sunset & Moonrise / Moonset Widgets -->
    <v-row dense>
      <v-col cols="12" md="6" class="mb-6">
        <sun-graph-widget />
      </v-col>
      <v-col cols="12" md="6" class="mb-6">
        <moon-graph-widget />
      </v-col>
    </v-row>
  </v-container>
</template>

<script setup lang="ts">
  import CurrentWeatherCard from '../components/Widgets/CurrentWeatherCard.vue'
  import DayForecastCard from '../components/Widgets/DayForecastCard.vue'
  import WeekForecastCard from '../components/Widgets/WeekForecastCard.vue'
  import SunGraphWidget from '../components/Widgets/SunGraphWidget.vue'
  import MoonGraphWidget from '../components/Widgets/MoonGraphWidget.vue'
</script>

<style scoped>
  /* Optional smooth scrolling for horizontal widgets */
  .v-slide-group {
    scroll-behavior: smooth;
  }
</style>
