<template>
  <v-card
    class="pa-4 rounded-xl mt-4"
    :style="{ background: colors.backgroundCard, color: colors.textPrimary }"
  >
    <h3 class="text-subtitle-1 font-weight-medium mb-2">Sunrise & Sunset</h3>
    <canvas ref="sunCanvas" style="width: 100%; height: 150px"></canvas>
    <div class="d-flex justify-space-between mt-2 px-2">
      <span :style="{ color: colors.textSecondary }">
        {{ sunrise ? formatTime(sunrise) : '--:--' }}
      </span>
      <span :style="{ color: colors.textSecondary }">
        {{ sunset ? formatTime(sunset) : '--:--' }}
      </span>
    </div>
  </v-card>
</template>

<script setup lang="ts">
  import { ref, onMounted, computed, watch } from 'vue'
  import { useThemeColors } from '@/composables/useThemeColors'
  import { useAppStore } from '@/stores/app'
  import type { Ref } from 'vue'

  const { colors } = useThemeColors()
  const sunCanvas: Ref<HTMLCanvasElement | null> = ref(null)

  const store = useAppStore()
  const current = computed(() => store.WeekForecast?.[0])

  // Get sunrise, sunset, and current time from the store
  const sunrise = computed(() =>
    current.value?.sunriseTime ? new Date(current.value.sunriseTime) : null
  )
  const sunset = computed(() =>
    current.value?.sunsetTime ? new Date(current.value.sunsetTime) : null
  )
  const now = computed(() =>
    current.value?.local_datetime ? new Date(current.value.local_datetime) : new Date()
  )

  const formatTime = (date: Date) =>
    date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })

  const drawArc = () => {
    if (!sunCanvas.value || !sunrise.value || !sunset.value) return
    const canvas = sunCanvas.value
    const ctx = canvas.getContext('2d')
    if (!ctx) return

    // High-DPI scaling
    const dpr = window.devicePixelRatio || 1
    canvas.width = canvas.clientWidth * dpr
    canvas.height = canvas.clientHeight * dpr
    ctx.scale(dpr, dpr)

    const width = canvas.clientWidth
    const height = canvas.clientHeight
    const padding = 1
    const radius = Math.min(width - padding, height - padding - 10)
    const centerX = width / 2
    const centerY = height - 10

    ctx.clearRect(0, 0, width, height)

    // Draw arc
    ctx.beginPath()
    ctx.lineWidth = 3
    ctx.strokeStyle = colors.value.accent
    ctx.arc(centerX, centerY, radius, Math.PI, 0, false)
    ctx.stroke()

    // Compute sun position
    const sunriseHour = sunrise.value.getHours() + sunrise.value.getMinutes() / 60
    const sunsetHour = sunset.value.getHours() + sunset.value.getMinutes() / 60
    const nowHour = now.value.getHours() + now.value.getMinutes() / 60

    const progress = Math.min(Math.max((nowHour - sunriseHour) / (sunsetHour - sunriseHour), 0), 1)
    const angle = Math.PI + progress * Math.PI

    const sunX = centerX + radius * Math.cos(angle)
    const sunY = centerY + radius * Math.sin(angle)

    // Draw sun
    ctx.beginPath()
    ctx.fillStyle = '#FFD700'
    ctx.arc(sunX, sunY, 8, 0, Math.PI * 2)
    ctx.fill()
  }

  onMounted(() => drawArc())
  watch([sunrise, sunset, now], drawArc)
</script>
