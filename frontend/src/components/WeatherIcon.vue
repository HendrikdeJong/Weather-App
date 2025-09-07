<template>
  <v-img
    v-if="src"
    :src="src"
    :alt="`Weather icon ${code}`"
    class="weather-icon"
    contain
    :style="{ width: `${props.size ?? 48}px`, height: `${props.size ?? 48}px` }"
  />
</template>

<script lang="ts" setup>
  import { computed } from 'vue'

  const props = defineProps<{
    code: number | null
    size?: number
  }>()

  // Replace this logic with your actual icon mapping
  const src = computed(() => {
    if (props.code === null) return null
    // Example: map code to icon file name
    return `/icons/weather-${props.code}.png`
  })
</script>

<style scoped>
  .weather-icon {
    /* Default size, overridden by inline style */
    width: 48px;
    height: 48px;
  }
</style>

<!-- 
<style scoped>
     .weather-icon {
       width: v-bind(size, 48px);
       height: v-bind(size, 48px);
     }
</style> -->
