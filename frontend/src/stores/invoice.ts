import { defineStore } from 'pinia'
import client from '../api/client'
import { db } from '../db'
import { syncService } from '../services/SyncService'

export interface InvoiceItem {
    id?: string
    invoice_id?: string
    product_id?: string | null
    description: string
    hsn_code?: string
    quantity: number
    unit_price: number
    tax_rate: number
    tax_amount: number
    cess_rate?: number
    cess_amount?: number
    discount?: number
    total: number
    name?: string
}

export interface Invoice {
    id: string
    business_id: string
    party_id: string
    invoice_number: string
    date: string
    due_date: string
    status: 'draft' | 'sent' | 'paid' | 'overdue' | 'void'
    subtotal: number
    tax_total: number
    cess_total?: number
    discount_total: number
    grand_total: number
    paid_amount: number
    notes?: string
    terms?: string
    challan_no?: string
    eway_bill_no?: string
    vehicle_no?: string
    po_number?: string
    type?: 'invoice' | 'credit_note' | 'quote'
    parent_id?: string
    reason?: string
    credit_notes?: Invoice[]; // Related credit notes
    meta?: {
        display_options?: {
            show_eway_details?: boolean
            show_hsn?: boolean
            show_gst_breakdown?: boolean
            show_discount?: boolean
            show_qr_bank_details?: boolean
            show_shipping_address?: boolean
        }
        billing_address?: any
        shipping_address?: any
        [key: string]: any
    }
    items: InvoiceItem[]
    party?: any
    business?: any
    allocations?: {
        id: string
        amount: number // Decimal (Rupees)
        payment: {
            id: string
            date: string
            amount: number // Decimal (Rupees)
            method: string
            reference?: string
            notes?: string
        }
    }[]
}

interface InvoiceState {
    invoices: Invoice[]
    currentInvoice: Invoice | null
    loading: boolean
    error: string | null
    pagination: {
        current_page: number
        per_page: number
        total: number
        last_page: number
    }
}

export const useInvoiceStore = defineStore('invoice', {
    state: (): InvoiceState => ({
        invoices: [],
        currentInvoice: null,
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
        async fetchInvoices(params: any = {}) {
            this.loading = true
            this.error = null
            try {
                const response = await client.get('/invoices', { params })
                this.invoices = response.data.data

                // Cache logic: Upsert fetched invoices
                if (response.data.data && response.data.data.length > 0) {
                    try {
                        const invoicesToCache = response.data.data.map((inv: any) => ({
                            ...inv,
                            // Ensure numeric fields are numbers if needed, mostly they come as is
                        }))
                        await db.invoices.bulkPut(invoicesToCache)
                    } catch (dbErr) {
                        console.error('Failed to cache invoices', dbErr)
                    }
                }

                this.pagination = {
                    current_page: response.data.current_page,
                    per_page: response.data.per_page,
                    total: response.data.total,
                    last_page: response.data.last_page
                }
            } catch (error: any) {
                if (!navigator.onLine) {
                    console.log('Offline: Loading invoices from cache')
                    try {
                        // Load from IndexedDB
                        // Note: Pagination won't really work offline for cached data unless we implement local pagination
                        // For now, just load all cached invoices sorted by date desc
                        const cachedInvoices = await db.invoices.toArray()
                        cachedInvoices.sort((a, b) => new Date(b.date).getTime() - new Date(a.date).getTime())

                        this.invoices = cachedInvoices as Invoice[]
                        return
                    } catch (dbErr) {
                        console.error('Failed to load cached invoices', dbErr)
                    }
                }

                this.error = error.response?.data?.message || 'Failed to fetch invoices'
                throw error
            } finally {
                this.loading = false
            }
        },

        async fetchInvoice(id: string) {
            this.loading = true
            this.error = null
            try {
                const response = await client.get(`/invoices/${id}`)
                this.currentInvoice = response.data
                // Cache individual invoice
                db.invoices.put(response.data).catch(e => console.error(e))
                return response.data
            } catch (error: any) {
                if (!navigator.onLine) {
                    try {
                        const cached = await db.invoices.get(id)
                        if (cached) {
                            this.currentInvoice = cached as Invoice
                            return cached
                        }
                    } catch (e) {/* ignore */ }
                }
                this.error = error.response?.data?.message || 'Failed to fetch invoice details'
                throw error
            } finally {
                this.loading = false
            }
        },

        async createInvoice(data: Partial<Invoice>) {
            this.loading = true
            try {
                const response = await client.post('/invoices', data)
                this.invoices.unshift(response.data)
                // Cache new invoice
                db.invoices.put(response.data).catch(e => console.error(e))
                return response.data
            } catch (error: any) {
                if (!navigator.onLine) {
                    // Offline Creation Logic
                    const tempId = `OFF_${Date.now()}`
                    const offlineInvoice = {
                        ...data,
                        id: tempId,
                        status: 'draft',
                        invoice_number: 'DRAFT-OFFLINE',
                        date: data.date || new Date().toISOString().split('T')[0],
                        due_date: data.due_date || new Date().toISOString().split('T')[0],
                        grand_total: data.grand_total || 0,
                        items: data.items || [],
                        // Add dummy business/party if needed strictly for UI, or rely on optional chaining in UI
                    } as Invoice

                    // Add to sync queue
                    await syncService.addToQueue('create_invoice', data)

                    // Add to local DB so it shows up in list
                    await db.invoices.add(offlineInvoice)

                    this.invoices.unshift(offlineInvoice)
                    alert('You are offline. Invoice saved locally and will sync when online.')
                    return offlineInvoice
                }

                this.error = error.response?.data?.message || 'Failed to create invoice'
                throw error
            } finally {
                this.loading = false
            }
        },

        async updateInvoice(id: string, data: Partial<Invoice>) {
            this.loading = true
            try {
                const response = await client.put(`/invoices/${id}`, data)
                const index = this.invoices.findIndex(i => i.id === id)
                if (index !== -1) {
                    this.invoices[index] = response.data
                }
                if (this.currentInvoice && this.currentInvoice.id === id) {
                    this.currentInvoice = response.data
                }
                return response.data
            } catch (error: any) {
                this.error = error.response?.data?.message || 'Failed to update invoice'
                throw error
            } finally {
                this.loading = false
            }
        },

        async deleteInvoice(id: string) {
            this.loading = true
            try {
                await client.delete(`/invoices/${id}`)
                this.invoices = this.invoices.filter(i => i.id !== id)
            } catch (error: any) {
                this.error = error.response?.data?.message || 'Failed to delete invoice'
                throw error
            } finally {
                this.loading = false
            }
        },

        async finalizeInvoice(id: string) {
            this.loading = true
            const index = this.invoices.findIndex(i => i.id === id)
            try {
                const response = await client.post(`/invoices/${id}/finalize`)
                if (index !== -1) {
                    this.invoices[index] = response.data
                }
                if (this.currentInvoice && this.currentInvoice.id === id) {
                    this.currentInvoice = response.data
                }
                return response.data
            } catch (error: any) {
                // If offline and we just mark as confirmed locally?
                // For finalize, enforcing backend check is better, but maybe:
                if (!navigator.onLine && index !== -1) {
                    const updated = { ...this.invoices[index], status: 'sent' } as Invoice
                    this.invoices[index] = updated
                    if (this.currentInvoice && this.currentInvoice.id === id) {
                        this.currentInvoice = updated
                    }
                    await db.invoices.put(updated) // Update local DB
                    await syncService.addToQueue('finalize_invoice', { id })
                    return updated
                }
                this.error = error.response?.data?.message || 'Failed to finalize invoice'
                throw error
            } finally {
                this.loading = false
            }
        },

        async duplicateInvoice(id: string) {
            this.loading = true
            try {
                const response = await client.post(`/invoices/${id}/duplicate`)
                this.invoices.unshift(response.data)
                return response.data
            } catch (error: any) {
                this.error = error.response?.data?.message || 'Failed to duplicate invoice'
                throw error
            } finally {
                this.loading = false
            }
        },

        async convertEstimateToInvoice(id: string) {
            this.loading = true
            try {
                const response = await client.post(`/invoices/${id}/convert`)
                this.invoices.unshift(response.data)
                return response.data
            } catch (error: any) {
                this.error = error.response?.data?.message || 'Failed to convert estimate'
                throw error
            } finally {
                this.loading = false
            }
        },

        async sendEmail(id: string) {
            try {
                await client.post(`/invoices/${id}/email`)
            } catch (error: any) {
                throw error
            }
        },

        async recordPayment(id: string, data: any) {
            try {
                // We post to /payments but it updates the invoice status/paid_amount usually
                const response = await client.post('/payments', {
                    invoice_id: id,
                    ...data
                })
                // Ideally we should reload the invoice here or update state
                // fetchInvoice(id) // But we can just let caller do it
                return response.data
            } catch (error: any) {
                throw error
            }
        },

        // Helper if needed to convert estimate on client side (or server if route existed)
        // For now, client logic resides in view, but we could move it here.
        // Let's keep it simple.

    }
})
