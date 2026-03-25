import { defineStore } from 'pinia'
import { ref } from 'vue'
import client from '../api/client'

export interface Product {
    id: string
    name: string
    sku?: string
    hsn_code?: string
    type: 'goods' | 'service'
    sale_price: number
    secondary_sale_price?: number
    purchase_price?: number
    secondary_purchase_price?: number
    tax_rate?: number
    cess_rate?: number
    is_tax_inclusive?: boolean
    current_stock?: number
    unit?: string
    secondary_unit?: string
    conversion_factor?: number
    description?: string
    status: 'active' | 'inactive'
}

export const useProductStore = defineStore('product', () => {
    const products = ref<Product[]>([])
    const currentProduct = ref<Product | null>(null)
    const loading = ref(false)
    const error = ref<string | null>(null)

    // Fetch all products with pagination support later if needed, primarily just list for now
    const fetchProducts = async (params = {}) => {
        loading.value = true
        error.value = null
        try {
            const response = await client.get('/products', { params })
            // Assuming API returns { data: [...], ... } or just [...]
            // Laravel paginate returns { data: [...], links: ..., meta: ... }
            products.value = response.data.data || response.data
            return products.value
        } catch (e: any) {
            error.value = e.response?.data?.message || 'Failed to fetch products'
            throw e
        } finally {
            loading.value = false
        }
    }

    const fetchProduct = async (id: string) => {
        loading.value = true
        try {
            const response = await client.get(`/products/${id}`)
            currentProduct.value = response.data
            return currentProduct.value
        } catch (e: any) {
            error.value = e.response?.data?.message || 'Failed to fetch product'
            throw e
        } finally {
            loading.value = false
        }
    }

    const createProduct = async (data: Partial<Product>) => {
        loading.value = true
        try {
            const response = await client.post('/products', data)
            products.value.unshift(response.data)
            return response.data
        } catch (e: any) {
            error.value = e.response?.data?.message || 'Failed to create product'
            throw e
        } finally {
            loading.value = false
        }
    }

    const updateProduct = async (id: string, data: Partial<Product>) => {
        loading.value = true
        try {
            const response = await client.put(`/products/${id}`, data)
            const index = products.value.findIndex(p => p.id === id)
            if (index !== -1) {
                products.value[index] = response.data
            }
            if (currentProduct.value && currentProduct.value.id === id) {
                currentProduct.value = response.data
            }
            return response.data
        } catch (e: any) {
            error.value = e.response?.data?.message || 'Failed to update product'
            throw e
        } finally {
            loading.value = false
        }
    }

    const deleteProduct = async (id: string) => {
        loading.value = true
        try {
            await client.delete(`/products/${id}`)
            products.value = products.value.filter(p => p.id !== id)
        } catch (e: any) {
            error.value = e.response?.data?.message || 'Failed to delete product'
            throw e
        } finally {
            loading.value = false
        }
    }

    return {
        products,
        currentProduct,
        loading,
        error,
        fetchProducts,
        fetchProduct,
        createProduct,
        updateProduct,
        deleteProduct
    }
})
