// composables/useThemeColors.ts
import { computed } from 'vue'
import { useTheme } from 'vuetify'

export function useThemeColors() {
  const theme = useTheme()

  const isDark = computed(() => theme.global.current.value.dark)

  const colors = computed(() => ({
    backgroundCard: isDark.value ? '#1c1c1c' : '#ffffff',
    textPrimary: isDark.value ? '#ffffff' : '#000000',
    textSecondary: isDark.value ? '#b0b0b0' : '#555555',
    accent: isDark.value ? '#4facfe' : '#00aaff',
    sunGradient: isDark.value
      ? 'linear-gradient(to bottom, #ffd70033, #ff8c0033)'
      : 'linear-gradient(to bottom, #ffdd33, #ffaa00)',
    moonGradient: isDark.value
      ? 'linear-gradient(to bottom, #a9a9a933, #55555533)'
      : 'linear-gradient(to bottom, #cccccc33, #99999933)',
  }))

  return { colors, isDark }
}
