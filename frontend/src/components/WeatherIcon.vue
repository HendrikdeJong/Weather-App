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
    time?: string
    size?: number
  }>()

  // Import all icons
  const icons = import.meta.glob('../assets/tomorrow-icons/*.png', {
    eager: true,
    import: 'default',
  })

  const src = computed<string | undefined>(() => {
    if (props.code === null) return undefined

    // Figure out hour → day/night suffix
    let hour = 12
    if (props.time) {
      const parsed = parseInt(props.time.slice(0, 2), 10)
      if (!isNaN(parsed)) hour = parsed
    }
    const isDay = hour >= 6 && hour < 18
    const suffix = isDay ? '0' : '1'

    // Try exact code+suffix
    const withSuffix = `../assets/tomorrow-icons/${props.code}${suffix}.png`
    if (icons[withSuffix]) return icons[withSuffix] as string

    // Fallback to base "day" (suffix 0)
    const fallback = `../assets/tomorrow-icons/${props.code}0.png`
    if (icons[fallback]) return icons[fallback] as string

    // Fallback to raw code (rare, but some sets use it)
    const plain = `../assets/tomorrow-icons/${props.code}.png`
    if (icons[plain]) return icons[plain] as string

    return undefined
  })
</script>
