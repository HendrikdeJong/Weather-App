<template>
  <v-app>
    <router-view />
  </v-app>
</template>

<script lang="ts" setup>
  import { onMounted } from 'vue'
  import { useLocationStore } from '@/stores/location'

  const location = useLocationStore()

  onMounted(() => {
    // Get location on app load
    location.fetchLocation()
  })
</script>
