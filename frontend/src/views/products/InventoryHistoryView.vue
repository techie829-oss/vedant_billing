<template>
    <AppLayout>
        <div class="p-fluid">
            <!-- Header Section -->
            <div class="flex flex-wrap items-center justify-between mb-6 gap-4">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 m-0">Inventory History</h1>
                    <p class="text-gray-500 mt-1">Audit trail of all stock movements and purchase transactions.</p>
                </div>
                <div>
                    <Button label="Back to Catalog" icon="pi pi-arrow-left" severity="secondary" outlined @click="$router.push('/products')" />
                </div>
            </div>

            <!-- Filters & Data Table -->
            <div class="grid grid-cols-12 gap-6">
                <!-- Sidebar Filters -->
                <div class="col-span-12 lg:col-span-3">
                    <Card class="border-none shadow-sm">
                        <template #title>Filter Activity</template>
                        <template #content>
                            <div class="flex flex-col gap-2">
                                <label class="font-semibold text-xs uppercase text-gray-500">Movement Type</label>
                                <Select v-model="filters.type" :options="typeOptions" optionLabel="label" optionValue="value" placeholder="All Movements" showClear />
                            </div>
                        </template>
                    </Card>
                </div>

                <!-- Main Data Table -->
                <div class="col-span-12 lg:col-span-9">
                    <Card class="border-none shadow-sm overflow-hidden">
                        <template #content>
                            <DataTable :value="transactions" :loading="loading" dataKey="id" 
                                :paginator="true" :rows="15" 
                                paginatorTemplate="FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink CurrentPageReport RowsPerPageDropdown"
                                :rowsPerPageOptions="[15, 30, 50]"
                                currentPageReportTemplate="Showing {first} to {last} of {totalRecords} movements"
                                responsiveLayout="stack" breakpoint="960px">
                                
                                <template #empty>No inventory movements found matching your filters.</template>

                                <Column field="created_at" header="Date / Time" sortable style="width: 180px">
                                    <template #body="{ data }">
                                        <div class="flex flex-col">
                                            <span class="font-medium text-gray-900">{{ formatDate(data.created_at) }}</span>
                                            <span class="text-[10px] text-gray-400 uppercase">{{ formatTime(data.created_at) }}</span>
                                        </div>
                                    </template>
                                </Column>

                                <Column field="product.name" header="Product" sortable>
                                    <template #body="{ data }">
                                        <div class="flex flex-col">
                                            <span class="font-bold text-gray-900">{{ data.product?.name || 'Unknown' }}</span>
                                            <span class="text-xs text-gray-500" v-if="data.product?.sku">{{ data.product.sku }}</span>
                                        </div>
                                    </template>
                                </Column>

                                <Column field="type" header="Type" sortable style="width: 120px">
                                    <template #body="{ data }">
                                        <Tag :value="data.type.toUpperCase()" :severity="getTypeSeverity(data.type)" />
                                    </template>
                                </Column>

                                <Column field="quantity" header="Quantity" sortable style="text-align: right; width: 120px">
                                    <template #body="{ data }">
                                        <span class="text-lg font-black" :class="data.quantity > 0 ? 'text-green-600' : 'text-red-600'">
                                            {{ data.quantity > 0 ? '+' : '' }}{{ Number(data.quantity) }}
                                        </span>
                                    </template>
                                </Column>

                                <Column field="unit_price" header="Value" sortable style="text-align: right; width: 120px">
                                    <template #body="{ data }">
                                        <span class="text-sm font-semibold text-gray-600">
                                            {{ data.unit_price ? '₹' + Number(data.unit_price).toFixed(2) : '-' }}
                                        </span>
                                    </template>
                                </Column>

                                <Column header="Reference / Party">
                                    <template #body="{ data }">
                                        <div class="flex flex-col">
                                            <span class="font-medium text-primary" v-if="data.party">{{ data.party.name }}</span>
                                            <span class="text-xs text-gray-500 italic" v-if="data.notes">{{ data.notes }}</span>
                                            <span v-if="!data.party && !data.notes" class="text-gray-300">-</span>
                                        </div>
                                    </template>
                                </Column>
                            </DataTable>
                        </template>
                    </Card>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import { ref, onMounted, watch } from 'vue'
import AppLayout from '../../layouts/AppLayout.vue'
import client from '../../api/client'

// PrimeVue
import Card from 'primevue/card'
import Button from 'primevue/button'
import DataTable from 'primevue/datatable'
import Column from 'primevue/column'
import Tag from 'primevue/tag'
import Select from 'primevue/select'

const transactions = ref<any[]>([])
const loading = ref(false)
const filters = ref({ type: '' })

const typeOptions = [
    { label: 'Purchase (Stock In)', value: 'purchase' },
    { label: 'Sale (Stock Out)', value: 'sale' },
    { label: 'Adjustment', value: 'adjustment' },
    { label: 'Return', value: 'return' }
]

const fetchHistory = async () => {
    loading.value = true
    try {
        const params: any = {}
        if (filters.value.type) params.type = filters.value.type
        const res = await client.get('/inventory', { params })
        transactions.value = res.data.data
    } catch (e) {
        console.error("Failed to load inventory history", e)
    } finally {
        loading.value = false
    }
}

watch(() => filters.value.type, fetchHistory)

const formatDate = (d: string) => new Date(d).toLocaleDateString('en-IN', { day: '2-digit', month: 'short', year: 'numeric' })
const formatTime = (d: string) => new Date(d).toLocaleTimeString('en-IN', { hour: '2-digit', minute: '2-digit' })

const getTypeSeverity = (type: string) => {
    switch (type) {
        case 'purchase': return 'success'
        case 'sale': return 'danger'
        case 'adjustment': return 'warn'
        case 'return': return 'info'
        default: return 'secondary'
    }
}

onMounted(fetchHistory)
</script>
