import { defineStore } from 'pinia'
import client from '../api/client'

export interface CashbookEntry {
    id: string
    date: string
    amount: number
    type: 'IN' | 'OUT'
    title: string // Customer Name or Category
    description: string
    payment_method?: string
}

interface CashbookSummary {
    total_in: number
    total_out: number
    balance: number
}

interface CashbookState {
    entries: CashbookEntry[]
    summary: CashbookSummary
    loading: boolean
    error: string | null
    pagination: {
        current_page: number
        per_page: number
        total: number
        last_page: number
    }
}

export const useCashbookStore = defineStore('cashbook', {
    state: (): CashbookState => ({
        entries: [],
        summary: { total_in: 0, total_out: 0, balance: 0 },
        loading: false,
        error: null,
        pagination: {
            current_page: 1,
            per_page: 20,
            total: 0,
            last_page: 1
        }
    }),

    actions: {
        async fetchCashbook(params: any = {}) {
            this.loading = true
            this.error = null
            try {
                const response = await client.get('/cashbook', { params })
                this.entries = response.data.entries.data
                this.summary = response.data.summary
                this.pagination = {
                    current_page: response.data.entries.current_page,
                    per_page: response.data.entries.per_page,
                    total: response.data.entries.total,
                    last_page: response.data.entries.last_page
                }
            } catch (error: any) {
                this.error = error.response?.data?.message || 'Failed to fetch cashbook'
                throw error
            } finally {
                this.loading = false
            }
        }
    }
})
