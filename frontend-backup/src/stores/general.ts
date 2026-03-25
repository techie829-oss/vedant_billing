import { defineStore } from 'pinia'
import client from '../api/client'

export const useGeneralStore = defineStore('general', {
    state: () => ({
        states: [] as { name: string, code: string }[],
        loadingStates: false
    }),

    actions: {
        async fetchStates() {
            if (this.states.length > 0) return

            this.loadingStates = true
            try {
                const response = await client.get('/gst-states')
                this.states = response.data
            } catch (error) {
                console.error('Failed to fetch states', error)
            } finally {
                this.loadingStates = false
            }
        }
    }
})
