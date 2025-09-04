// src/stores/location.ts
import { defineStore } from 'pinia'

export const useLocationStore = defineStore('location', {
  state: () => ({
    latitude: null as number | null,
    longitude: null as number | null,
    error: null as string | null,
    loading: false,
  }),

  actions: {
    async fetchLocation() {
      this.loading = true
      this.error = null

      if (!('geolocation' in navigator)) {
        this.error = 'Geolocation not supported'
        this.loading = false
        return
      }

      return new Promise<void>((resolve) => {
        navigator.geolocation.getCurrentPosition(
          (position) => {
            this.latitude = position.coords.latitude
            this.longitude = position.coords.longitude
            this.loading = false
            resolve()
          },
          (err) => {
            this.error = err.message || 'Failed to get location'
            this.loading = false
            resolve()
          }
        )
      })
    },
  },
})
