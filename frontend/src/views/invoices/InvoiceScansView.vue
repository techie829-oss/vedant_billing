<template>
    <AppLayout>
        <div class="p-fluid">
            <!-- Header Section -->
            <div class="flex flex-wrap items-center justify-between mb-6 gap-4">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 m-0">Catalog AI Scans</h1>
                    <p class="text-gray-500 mt-1">Manage scanned price lists and auto-extracted product data.</p>
                </div>
                <div>
                    <Button label="Scan New Catalog" icon="pi pi-expand" @click="showUploadModal = true" />
                </div>
            </div>

            <!-- Stats Row -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
                <Card class="border-none shadow-sm overflow-hidden bg-gray-50">
                    <template #content>
                        <div class="flex flex-col">
                            <span class="text-xs font-bold text-gray-500 uppercase">Total Scans</span>
                            <span class="text-2xl font-black text-gray-900">{{ stats.total }}</span>
                        </div>
                    </template>
                </Card>
                <Card class="border-none shadow-sm overflow-hidden bg-green-50">
                    <template #content>
                        <div class="flex flex-col">
                            <span class="text-xs font-bold text-green-600 uppercase">Successful</span>
                            <span class="text-2xl font-black text-green-900">{{ stats.success }}</span>
                        </div>
                    </template>
                </Card>
                <Card class="border-none shadow-sm overflow-hidden bg-amber-50">
                    <template #content>
                        <div class="flex flex-col">
                            <span class="text-xs font-bold text-amber-600 uppercase">Processing</span>
                            <span class="text-2xl font-black text-amber-900">{{ stats.pending }}</span>
                        </div>
                    </template>
                </Card>
                <Card class="border-none shadow-sm overflow-hidden bg-red-50">
                    <template #content>
                        <div class="flex flex-col">
                            <span class="text-xs font-bold text-red-600 uppercase">Failed</span>
                            <span class="text-2xl font-black text-red-900">{{ stats.failed }}</span>
                        </div>
                    </template>
                </Card>
            </div>

            <!-- Filters Card -->
            <Card class="border-none shadow-sm mb-6">
                <template #content>
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                        <div class="flex flex-col gap-2">
                            <label class="font-semibold text-xs uppercase text-gray-500">Status</label>
                            <Select v-model="filters.status" :options="statusOptions" optionLabel="label" optionValue="value" placeholder="All Scans" showClear @change="fetchScans" />
                        </div>
                        <div class="flex flex-col gap-2">
                            <label class="font-semibold text-xs uppercase text-gray-500">From Date</label>
                            <DatePicker v-model="filters.from_date" dateFormat="yy-mm-dd" showIcon @change="fetchScans" />
                        </div>
                        <div class="flex flex-col gap-2">
                            <label class="font-semibold text-xs uppercase text-gray-500">To Date</label>
                            <DatePicker v-model="filters.to_date" dateFormat="yy-mm-dd" showIcon @change="fetchScans" />
                        </div>
                        <div>
                            <Button icon="pi pi-refresh" severity="secondary" outlined @click="fetchScans" :loading="loading" />
                        </div>
                    </div>
                </template>
            </Card>

            <!-- Main Data Table -->
            <Card class="border-none shadow-sm overflow-hidden">
                <template #content>
                    <DataTable :value="scans" :loading="loading" dataKey="id" 
                        :paginator="true" :rows="10" 
                        paginatorTemplate="FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink CurrentPageReport RowsPerPageDropdown"
                        :rowsPerPageOptions="[10, 25, 50]"
                        currentPageReportTemplate="Showing {first} to {last} of {totalRecords} scans"
                        responsiveLayout="stack" breakpoint="960px">
                        
                        <template #empty>No scans found matching your criteria.</template>

                        <Column field="created_at" header="Date / Time" sortable style="width: 180px">
                            <template #body="{ data }">
                                <div class="flex flex-col">
                                    <span class="font-medium text-gray-900">{{ formatDate(data.created_at) }}</span>
                                    <span class="text-[10px] text-gray-400 uppercase">{{ formatTime(data.created_at) }}</span>
                                </div>
                            </template>
                        </Column>

                        <Column header="Catalog Details">
                            <template #body="{ data }">
                                <div class="flex flex-col">
                                    <span class="font-bold text-gray-900">{{ data.vendor_name || 'Unknown Vendor' }}</span>
                                    <span class="text-xs text-gray-500" v-if="data.invoice_number">Ref: {{ data.invoice_number }}</span>
                                </div>
                            </template>
                        </Column>

                        <Column field="temp_products_count" header="Products" sortable style="width: 100px; text-align: center">
                            <template #body="{ data }">
                                <Tag :value="data.temp_products_count || 0" severity="secondary" />
                            </template>
                        </Column>

                        <Column field="status" header="Status" sortable style="width: 150px">
                            <template #body="{ data }">
                                <Tag :value="getStatusDisplay(data).label" :severity="getStatusDisplay(data).severity" />
                            </template>
                        </Column>

                        <Column header="Actions" headerStyle="width: 10rem; text-align: center" bodyStyle="text-align: center; overflow: visible">
                            <template #body="{ data }">
                                <div class="flex justify-center gap-1">
                                    <Button icon="pi pi-eye" severity="secondary" rounded text v-tooltip.top="'View Extracted Data'" 
                                        @click="viewScan(data)" :disabled="data.status !== 'success'" />
                                    <Button v-if="data.status === 'failed'" icon="pi pi-refresh" severity="info" rounded text v-tooltip.top="'Retry OCR'" 
                                        @click="retryScan(data.id)" />
                                    <Button icon="pi pi-trash" severity="danger" rounded text v-tooltip.top="'Delete'" 
                                        @click="deleteScan(data.id)" />
                                </div>
                            </template>
                        </Column>
                    </DataTable>
                </template>
            </Card>
        </div>

        <!-- Upload Dialog -->
        <Dialog v-model:visible="showUploadModal" header="Scan Catalog" :modal="true" :style="{ width: '450px' }">
            <div class="flex flex-col gap-6 pt-4">
                <p class="text-gray-500">Upload a photo of your product catalog or price list. Our AI will automatically extract product names, prices, and quantities.</p>
                
                <div v-if="!uploading" class="flex flex-col items-center justify-center p-10 border-2 border-dashed border-gray-300 rounded-2xl hover:border-primary transition-colors cursor-pointer" @click="fileInput?.click()">
                    <i class="pi pi-cloud-upload text-4xl text-gray-300 mb-4"></i>
                    <span class="font-bold">Click to upload image</span>
                    <span class="text-xs text-gray-400 mt-1 uppercase">PNG, JPG or PDF</span>
                    <input type="file" ref="fileInput" accept="image/*,application/pdf" @change="handleFileSelect" class="hidden">
                </div>

                <div v-else class="flex flex-col items-center justify-center p-10">
                    <ProgressSpinner style="width: 50px; height: 50px" />
                    <p class="mt-4 font-bold">Uploading to secure server...</p>
                </div>
            </div>
            <template #footer>
                <Button label="Cancel" text severity="secondary" @click="showUploadModal = false" />
            </template>
        </Dialog>
    </AppLayout>
</template>

<script setup lang="ts">
import { onMounted, onUnmounted, ref } from 'vue'
import { useRouter } from 'vue-router'
import AppLayout from '../../layouts/AppLayout.vue'
import client from '../../api/client'

// PrimeVue
import Card from 'primevue/card'
import Button from 'primevue/button'
import DataTable from 'primevue/datatable'
import Column from 'primevue/column'
import Tag from 'primevue/tag'
import Select from 'primevue/select'
import DatePicker from 'primevue/datepicker'
import Dialog from 'primevue/dialog'
import ProgressSpinner from 'primevue/progressspinner'

const router = useRouter()

const scans = ref<any[]>([])
const loading = ref(false)
const showUploadModal = ref(false)
const uploading = ref(false)
const fileInput = ref<HTMLInputElement | null>(null)
let pollTimer: any = null

const filters = ref({ status: '', from_date: null, to_date: null })
const statusOptions = [
    { label: 'Processing', value: 'pending' },
    { label: 'Successful', value: 'success' },
    { label: 'Failed', value: 'failed' }
]

const stats = ref({ total: 0, success: 0, pending: 0, failed: 0 })

onMounted(fetchScans)
onUnmounted(() => { if (pollTimer) clearTimeout(pollTimer) })

function schedulePoll() {
    if (pollTimer) clearTimeout(pollTimer)
    const hasPending = scans.value.some(s => s.status === 'pending')
    if (hasPending) pollTimer = setTimeout(fetchScans, 5000)
}

async function fetchScans() {
    loading.value = scans.value.length === 0
    try {
        const params: any = {}
        if (filters.value.status) params.status = filters.value.status
        if (filters.value.from_date) params.from_date = (filters.value.from_date as any).toISOString().split('T')[0]
        if (filters.value.to_date) params.to_date = (filters.value.to_date as any).toISOString().split('T')[0]

        const response = await client.get('/invoice-scans', { params })
        scans.value = response.data.data.data || response.data.data

        stats.value.total = scans.value.length
        stats.value.success = scans.value.filter(s => s.status === 'success').length
        stats.value.pending = scans.value.filter(s => s.status === 'pending').length
        stats.value.failed = scans.value.filter(s => s.status === 'failed').length

        schedulePoll()
    } finally { loading.value = false }
}

function handleFileSelect(event: Event) {
    const target = event.target as HTMLInputElement
    const file = target.files?.[0]
    if (file) uploadInvoice(file)
}

async function uploadInvoice(file: File) {
    uploading.value = true
    try {
        const fd = new FormData(); fd.append('invoice', file)
        await client.post('/products/scan-invoice', fd, { headers: { 'Content-Type': 'multipart/form-data' } })
        showUploadModal.value = false
        await fetchScans()
    } catch (e: any) { alert(e.response?.data?.message || 'Upload failed') } 
    finally { uploading.value = false; if (fileInput.value) fileInput.value.value = '' }
}

async function retryScan(scanId: string) {
    await client.post(`/invoice-scans/${scanId}/retry`)
    await fetchScans()
}

async function deleteScan(scanId: string) {
    if (!confirm('Are you sure you want to delete this scan?')) return
    await client.delete(`/invoice-scans/${scanId}`)
    await fetchScans()
}

function viewScan(scan: any) { if (scan.status === 'success') router.push(`/invoice-scans/${scan.id}`) }
const formatDate = (d: string) => new Date(d).toLocaleDateString('en-IN', { day: '2-digit', month: 'short', year: 'numeric' })
const formatTime = (d: string) => new Date(d).toLocaleTimeString('en-IN', { hour: '2-digit', minute: '2-digit' })

function getStatusDisplay(scan: any) {
    if (scan.status === 'pending') return { label: 'PROCESSING', severity: 'warn' }
    if (scan.status === 'failed') return { label: 'FAILED', severity: 'danger' }
    if (!scan.is_fully_mapped) return { label: 'ACTION NEEDED', severity: 'warn' }
    if (scan.invoice_id) return { label: 'PROCESSED', severity: 'info' }
    return { label: 'READY', severity: 'success' }
}
</script>
