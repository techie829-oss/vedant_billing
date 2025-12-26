import { defineStore } from 'pinia'
import { ref } from 'vue'
import client from '../api/client'

export const useReportStore = defineStore('reports', () => {
    const loading = ref(false)
    const error = ref<string | null>(null)

    async function fetchSalesReport(params: any) {
        loading.value = true
        error.value = null
        try {
            const res = await client.get('/reports/sales', { params })
            return res.data
        } catch (e: any) {
            error.value = e.response?.data?.message || 'Failed to fetch sales report'
            throw e
        } finally {
            loading.value = false
        }
    }

    async function fetchOutstandingReport(params: any = {}) {
        loading.value = true
        error.value = null
        try {
            const res = await client.get('/reports/outstanding', { params })
            return res.data
        } catch (e: any) {
            error.value = e.response?.data?.message || 'Failed to fetch outstanding report'
            throw e
        } finally {
            loading.value = false
        }
    }

    async function fetchStockReport(params: any = {}) {
        loading.value = true
        error.value = null
        try {
            const res = await client.get('/reports/stock', { params })
            return res.data
        } catch (e: any) {
            error.value = e.response?.data?.message || 'Failed to fetch stock report'
            throw e
        } finally {
            loading.value = false
        }
    }

    async function fetchProfitLoss(params: any = {}) {
        loading.value = true
        error.value = null
        try {
            const res = await client.get('/reports/profit-loss', { params })
            return res.data
        } catch (e: any) {
            error.value = e.response?.data?.message || 'Failed to fetch profit & loss report'
            throw e
        } finally {
            loading.value = false
        }
    }

    return {
        loading,
        error,
        fetchSalesReport,
        fetchOutstandingReport,
        fetchStockReport,
        fetchProfitLoss
    }
})
