import { defineStore } from 'pinia'
import client from '../api/client'

export const useConfigStore = defineStore('config', {
    state: () => ({
        data: null as any,
        loading: false,
    }),
    actions: {
        async fetchConfig() {
            if (this.data) return this.data // Return cached
            if (this.loading) return
            
            this.loading = true
            try {
                const response = await client.get('/config')
                this.data = response.data
            } catch (error) {
                console.error('Failed to load system config', error)
            } finally {
                this.loading = false
            }
            return this.data
        }
    }
})
