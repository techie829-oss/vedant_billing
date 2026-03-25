import { defineStore } from 'pinia'
import client from '../api/client'

export interface Expense {
    id: string
    business_id: string
    category: string
    amount: number
    date: string
    description?: string
    payment_method?: string
    reference_no?: string
    created_at?: string
    updated_at?: string
}

interface ExpenseState {
    expenses: Expense[]
    loading: boolean
    error: string | null
    pagination: {
        current_page: number
        per_page: number
        total: number
        last_page: number
    }
}

export const useExpenseStore = defineStore('expense', {
    state: (): ExpenseState => ({
        expenses: [],
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
        async fetchExpenses(params: any = {}) {
            this.loading = true
            this.error = null
            try {
                const response = await client.get('/expenses', { params })
                this.expenses = response.data.data
                this.pagination = {
                    current_page: response.data.current_page,
                    per_page: response.data.per_page,
                    total: response.data.total,
                    last_page: response.data.last_page
                }
            } catch (error: any) {
                this.error = error.response?.data?.message || 'Failed to fetch expenses'
                throw error
            } finally {
                this.loading = false
            }
        },

        async createExpense(data: Partial<Expense>) {
            this.loading = true
            try {
                const response = await client.post('/expenses', data)
                this.expenses.unshift(response.data)
                return response.data
            } catch (error: any) {
                this.error = error.response?.data?.message || 'Failed to create expense'
                throw error
            } finally {
                this.loading = false
            }
        },

        async updateExpense(id: string, data: Partial<Expense>) {
            this.loading = true
            try {
                const response = await client.put(`/expenses/${id}`, data)
                const index = this.expenses.findIndex(e => e.id === id)
                if (index !== -1) {
                    this.expenses[index] = response.data
                }
                return response.data
            } catch (error: any) {
                this.error = error.response?.data?.message || 'Failed to update expense'
                throw error
            } finally {
                this.loading = false
            }
        },

        async deleteExpense(id: string) {
            this.loading = true
            try {
                await client.delete(`/expenses/${id}`)
                this.expenses = this.expenses.filter(e => e.id !== id)
            } catch (error: any) {
                this.error = error.response?.data?.message || 'Failed to delete expense'
                throw error
            } finally {
                this.loading = false
            }
        }
    }
})
