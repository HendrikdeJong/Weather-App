<template>
  <v-card
    class="pa-4 rounded-xl mt-4"
    :style="{ background: colors.backgroundCard, color: colors.textPrimary }"
  >
    <h3 class="text-subtitle-1 font-weight-medium mb-2">Moonrise & Moonset</h3>
    <canvas ref="moonCanvas" style="width: 100%; height: 150px"></canvas>
    <div class="d-flex justify-space-between mt-2 px-2">
      <span :style="{ color: colors.textSecondary }">
        {{ moonrise ? formatTime(moonrise) : '--:--' }}
      </span>
      <span :style="{ color: colors.textSecondary }">
        {{ moonset ? formatTime(moonset) : '--:--' }}
      </span>
    </div>
  </v-card>
</template>

<script setup lang="ts">
  import { ref, onMounted, computed, watch } from 'vue'
  import { useThemeColors } from '@/composables/useThemeColors'
  // import { useAppStore } from '@/stores/app' // Uncomment and use your store if needed
  import type { Ref } from 'vue'

  const { colors } = useThemeColors()
  const moonCanvas: Ref<HTMLCanvasElement | null> = ref(null)

  // Example data (replace with your store data)
  const moonrise = computed(() => new Date('2025-09-07T20:15:00Z'))
  const moonset = computed(() => new Date('2025-09-08T07:03:00Z'))
  const now = computed(() => new Date('2025-09-08T01:00:00Z'))

  const formatTime = (date: Date) =>
    date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })

  const drawArc = () => {
    if (!moonCanvas.value || !moonrise.value || !moonset.value) return
    const canvas = moonCanvas.value
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
    const radius = Math.min(width - padding, height - padding - 20) / 2
    const centerX = width / 2
    const centerY = height - 20

    ctx.clearRect(0, 0, width, height)

    // Draw arc
    ctx.beginPath()
    ctx.lineWidth = 3
    ctx.strokeStyle = colors.value.accent
    ctx.arc(centerX, centerY, radius, Math.PI, 0, false)
    ctx.stroke()

    // Compute moon position (handle moonset after midnight)
    let moonriseHour = moonrise.value.getHours() + moonrise.value.getMinutes() / 60
    let moonsetHour = moonset.value.getHours() + moonset.value.getMinutes() / 60
    let nowHour = now.value.getHours() + now.value.getMinutes() / 60

    if (moonsetHour < moonriseHour) moonsetHour += 24
    if (nowHour < moonriseHour) nowHour += 24

    const progress = Math.min(
      Math.max((nowHour - moonriseHour) / (moonsetHour - moonriseHour), 0),
      1
    )
    const angle = Math.PI + progress * Math.PI

    const moonX = centerX + radius * Math.cos(angle)
    const moonY = centerY + radius * Math.sin(angle)

    // Draw moon
    ctx.beginPath()
    ctx.fillStyle = '#B0C4DE'
    ctx.arc(moonX, moonY, 10, 0, Math.PI * 2)
    ctx.fill()
  }

  onMounted(() => drawArc())
  watch([moonrise, moonset, now], drawArc)
</script>
