import { defineStore } from 'pinia'
import client from '../api/client'

export interface Party {
    id: string
    business_id: string
    party_type: 'customer' | 'vendor'
    name: string
    email?: string
    phone?: string
    gstin?: string
    pan?: string
    notes?: string
    billing_address?: any
    shipping_address?: any
    opening_balance: number
    current_balance: number
    status: 'active' | 'inactive'
    created_at?: string
    updated_at?: string
}

export const usePartyStore = defineStore('party', {
    state: () => ({
        parties: [] as Party[],
        party: null as Party | null,
        loading: false,
        error: null as string | null,
        pagination: {
            current_page: 1,
            last_page: 1,
            total: 0,
            per_page: 15
        }
    }),

    getters: {
        customers: (state) => state.parties.filter(p => p.party_type === 'customer'),
        vendors: (state) => state.parties.filter(p => p.party_type === 'vendor')
    },

    actions: {
        async fetchParties(params: any = {}) {
            this.loading = true
            this.error = null
            try {
                const response = await client.get('/parties', { params })
                // Assuming backend returns paginated response or plain array. 
                // Adjusting for Laravel's default pagination structure if used, 
                // otherwise handling plain array
                if (response.data.data) {
                    this.parties = response.data.data
                    this.pagination = {
                        current_page: response.data.current_page,
                        last_page: response.data.last_page,
                        total: response.data.total,
                        per_page: response.data.per_page
                    }
                } else {
                    this.parties = response.data
                }
            } catch (err: any) {
                this.error = err.response?.data?.message || 'Failed to fetch parties'
                console.error(err)
            } finally {
                this.loading = false
            }
        },

        async fetchParty(id: string) {
            this.loading = true
            this.error = null
            try {
                const response = await client.get(`/parties/${id}`)
                this.party = response.data
                return this.party
            } catch (err: any) {
                this.error = err.response?.data?.message || 'Failed to fetch party'
                console.error(err)
                throw err
            } finally {
                this.loading = false
            }
        },

        async createParty(partyData: Partial<Party>) {
            this.loading = true
            this.error = null
            try {
                const response = await client.post('/parties', partyData)
                this.parties.unshift(response.data)
                return response.data
            } catch (err: any) {
                this.error = err.response?.data?.message || 'Failed to create party'
                throw err
            } finally {
                this.loading = false
            }
        },

        async updateParty(id: string, partyData: Partial<Party>) {
            this.loading = true
            this.error = null
            try {
                const response = await client.put(`/parties/${id}`, partyData)
                const index = this.parties.findIndex(p => p.id === id)
                if (index !== -1) {
                    this.parties[index] = response.data
                }
                this.party = response.data
                return response.data
            } catch (err: any) {
                this.error = err.response?.data?.message || 'Failed to update party'
                throw err
            } finally {
                this.loading = false
            }
        },

        async deleteParty(id: string) {
            this.loading = true
            this.error = null
            try {
                await client.delete(`/parties/${id}`)
                this.parties = this.parties.filter(p => p.id !== id)
            } catch (err: any) {
                this.error = err.response?.data?.message || 'Failed to delete party'
                throw err
            } finally {
                this.loading = false
            }
        }
    }
})
