<template>
    <AppLayout>
        <div class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Product Catalog Scans</h1>
                <p class="text-sm text-gray-500 mt-1">View and manage all scanned product catalogs and price lists</p>
            </div>
            <div class="mt-4 sm:mt-0 flex space-x-3">
                <button @click="showUploadModal = true"
                    class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-lg text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none transition-colors">
                    <svg class="-ml-1 mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Scan New Catalog
                </button>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 gap-5 sm:grid-cols-4 mb-8">
            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg class="h-6 w-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Total Scans</dt>
                                <dd class="text-lg font-semibold text-gray-900">{{ stats.total }}</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>
            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg class="h-6 w-6 text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Successful</dt>
                                <dd class="text-lg font-semibold text-green-600">{{ stats.success }}</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>
            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg class="h-6 w-6 text-yellow-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Processing</dt>
                                <dd class="text-lg font-semibold text-yellow-600">{{ stats.pending }}</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>
            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg class="h-6 w-6 text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Failed</dt>
                                <dd class="text-lg font-semibold text-red-600">{{ stats.failed }}</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="bg-white shadow rounded-lg p-4 mb-6">
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                    <select v-model="filters.status" @change="fetchScans"
                        class="block w-full rounded-lg border border-gray-300 px-3 py-2 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm transition-colors">
                        <option value="">All Scans</option>
                        <option value="pending">Processing</option>
                        <option value="success">Successful</option>
                        <option value="failed">Failed</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">From Date</label>
                    <input type="date" v-model="filters.from_date" @change="fetchScans"
                        class="block w-full rounded-lg border border-gray-300 px-3 py-2 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm transition-colors text-gray-600">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">To Date</label>
                    <input type="date" v-model="filters.to_date" @change="fetchScans"
                        class="block w-full rounded-lg border border-gray-300 px-3 py-2 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm transition-colors text-gray-600">
                </div>
            </div>
        </div>

        <!-- Scans List -->
        <div class="bg-white shadow rounded-lg overflow-hidden">
            <div v-if="loading" class="text-center py-12">
                <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600"></div>
                <p class="mt-2 text-sm text-gray-500">Loading scans...</p>
            </div>

            <div v-else-if="scans.length === 0" class="text-center py-12">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">No scans found</h3>
                <p class="mt-1 text-sm text-gray-500">Get started by scanning your first catalog or price list.</p>
                <div class="mt-6">
                    <button @click="showUploadModal = true"
                        class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700">
                        Scan Catalog
                    </button>
                </div>
            </div>

            <ul v-else role="list" class="divide-y divide-gray-200">
                <li v-for="scan in scans" :key="scan.id" class="p-4 hover:bg-gray-50 cursor-pointer"
                    @click="viewScan(scan)">
                    <div class="flex items-center justify-between">
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center space-x-3">
                                <span
                                    class="inline-flex items-center rounded-md px-2 py-1 text-xs font-medium ring-1 ring-inset"
                                    :class="{
                                        'bg-green-50 text-green-700 ring-green-600/20': scan.status === 'success',
                                        'bg-yellow-50 text-yellow-700 ring-yellow-600/20': scan.status === 'pending',
                                        'bg-red-50 text-red-700 ring-red-600/20': scan.status === 'failed'
                                    }">
                                    {{ scan.status === 'success' ? 'Completed' : scan.status === 'pending' ?
                                        'Processing…' : 'Failed' }}
                                    <span v-if="scan.status === 'pending'"
                                        class="ml-1 inline-block w-2 h-2 rounded-full bg-yellow-500 animate-pulse"></span>
                                </span>
                                <p class="text-sm font-medium text-gray-900">
                                    {{ scan.vendor_name || 'Unknown Vendor' }}
                                </p>
                                <p class="text-sm text-gray-500">
                                    Ref: {{ scan.invoice_number || '-' }}
                                </p>
                            </div>
                            <div class="mt-2 flex items-center text-sm text-gray-500 space-x-4">
                                <span>{{ formatDate(scan.created_at) }}</span>
                                <span v-if="scan.temp_products_count">{{ scan.temp_products_count }} products</span>
                                <span v-if="scan.error_message" class="text-red-600">{{ scan.error_message }}</span>
                            </div>
                        </div>
                        <div class="flex items-center space-x-2" @click.stop>
                            <button v-if="scan.status === 'failed'" @click="retryScan(scan.id)"
                                class="inline-flex items-center px-3 py-1 border border-gray-300 text-xs font-medium rounded text-gray-700 bg-white hover:bg-gray-50">
                                <svg class="mr-1 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                </svg>
                                Retry
                            </button>
                            <button @click="deleteScan(scan.id)"
                                class="inline-flex items-center px-3 py-1 border border-red-300 text-xs font-medium rounded text-red-700 bg-white hover:bg-red-50">
                                <svg class="mr-1 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                                Delete
                            </button>
                        </div>
                    </div>
                </li>
            </ul>
        </div>

        <!-- Upload Modal (reuse from ProductListView) -->
        <div v-if="showUploadModal" class="relative z-50" @click.self="showUploadModal = false">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"></div>
            <div class="fixed inset-0 z-50 overflow-y-auto">
                <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                    <div
                        class="relative transform overflow-hidden rounded-lg bg-white px-4 pb-4 pt-5 text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg sm:p-6">
                        <div>
                            <h3 class="text-lg font-semibold leading-6 text-gray-900">Scan Product Catalog</h3>
                            <p class="mt-1 text-sm text-gray-500">Upload a catalog or list page to extract products</p>
                        </div>

                        <div v-if="!uploading" class="mt-6">
                            <input type="file" ref="fileInput" accept="image/*" @change="handleFileSelect"
                                class="hidden">
                            <div @click="fileInput?.click()"
                                class="mt-2 flex justify-center rounded-lg border border-dashed border-gray-900/25 px-6 py-10 cursor-pointer hover:border-indigo-500 transition-colors">
                                <div class="text-center">
                                    <svg class="mx-auto h-12 w-12 text-gray-300" viewBox="0 0 24 24"
                                        fill="currentColor">
                                        <path fill-rule="evenodd"
                                            d="M1.5 6a2.25 2.25 0 012.25-2.25h16.5A2.25 2.25 0 0122.5 6v12a2.25 2.25 0 01-2.25 2.25H3.75A2.25 2.25 0 011.5 18V6zM3 16.06V18c0 .414.336.75.75.75h16.5A.75.75 0 0021 18v-1.94l-2.69-2.689a1.5 1.5 0 00-2.12 0l-.88.879.97.97a.75.75 0 11-1.06 1.06l-5.16-5.159a1.5 1.5 0 00-2.12 0L3 16.061zm10.125-7.81a1.125 1.125 0 112.25 0 1.125 1.125 0 01-2.25 0z"
                                            clip-rule="evenodd" />
                                    </svg>
                                    <div class="mt-4 text-sm leading-6 text-gray-600">
                                        <span class="font-semibold text-indigo-600">Click to upload</span>
                                        <span class="pl-1">or drag and drop</span>
                                    </div>
                                    <p class="text-xs leading-5 text-gray-600">PNG, JPG up to 10MB</p>
                                </div>
                            </div>
                        </div>

                        <div v-else class="mt-6 text-center py-8">
                            <div class="inline-block animate-spin rounded-full h-10 w-10 border-b-2 border-indigo-600">
                            </div>
                            <p class="mt-3 text-sm text-gray-600">Uploading...</p>
                        </div>

                        <div class="mt-5 sm:mt-6">
                            <button @click="showUploadModal = false" type="button"
                                class="inline-flex w-full justify-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50">
                                Close
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import { onMounted, onUnmounted, ref } from 'vue'
import { useRouter } from 'vue-router'
import AppLayout from '../../layouts/AppLayout.vue'
import client from '../../api/client'

const router = useRouter()

const scans = ref<any[]>([])
const loading = ref(false)
const showUploadModal = ref(false)
const uploading = ref(false)
const fileInput = ref<HTMLInputElement | null>(null)
let pollTimer: ReturnType<typeof setTimeout> | null = null

const filters = ref({
    status: '',
    from_date: '',
    to_date: ''
})

const stats = ref({
    total: 0,
    success: 0,
    pending: 0,
    failed: 0
})

onMounted(() => {
    fetchScans()
})

onUnmounted(() => {
    if (pollTimer) clearTimeout(pollTimer)
})

function schedulePoll() {
    if (pollTimer) clearTimeout(pollTimer)
    const hasPending = scans.value.some(s => s.status === 'pending')
    if (hasPending) {
        pollTimer = setTimeout(async () => {
            await fetchScans()
        }, 5000)
    }
}

async function fetchScans() {
    loading.value = loading.value ? true : scans.value.length === 0
    try {
        const params = new URLSearchParams()
        if (filters.value.status) params.append('status', filters.value.status)
        if (filters.value.from_date) params.append('from_date', filters.value.from_date)
        if (filters.value.to_date) params.append('to_date', filters.value.to_date)

        const response = await client.get(`/invoice-scans?${params.toString()}`)
        scans.value = response.data.data.data || response.data.data

        // Calculate stats
        stats.value.total = scans.value.length
        stats.value.success = scans.value.filter(s => s.status === 'success').length
        stats.value.pending = scans.value.filter(s => s.status === 'pending').length
        stats.value.failed = scans.value.filter(s => s.status === 'failed').length

        schedulePoll()
    } catch (error) {
        console.error('Failed to fetch scans:', error)
    } finally {
        loading.value = false
    }
}

function handleFileSelect(event: Event) {
    const target = event.target as HTMLInputElement
    const file = target.files?.[0]
    if (!file) return

    uploadInvoice(file)
}

async function uploadInvoice(file: File) {
    uploading.value = true
    const formData = new FormData()
    formData.append('invoice', file)

    try {
        const response = await client.post('/products/scan-invoice', formData, {
            headers: { 'Content-Type': 'multipart/form-data' }
        })

        if (response.data.success) {
            showUploadModal.value = false
            await fetchScans()
        }
    } catch (error: any) {
        alert(error.response?.data?.message || 'Upload failed')
    } finally {
        uploading.value = false
    }
}

async function retryScan(scanId: string) {
    try {
        await client.post(`/invoice-scans/${scanId}/retry`)
        await fetchScans()
    } catch (error: any) {
        alert(error.response?.data?.message || 'Retry failed')
    }
}

async function deleteScan(scanId: string) {
    if (!confirm('Are you sure you want to delete this scan?')) return

    try {
        await client.delete(`/invoice-scans/${scanId}`)
        await fetchScans()
    } catch (error: any) {
        alert(error.response?.data?.message || 'Delete failed')
    }
}

function viewScan(scan: any) {
    if (scan.status === 'success') {
        router.push(`/invoice-scans/${scan.id}`)
    }
}

function formatDate(dateString: string) {
    return new Date(dateString).toLocaleString()
}
</script>
