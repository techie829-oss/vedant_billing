<template>
    <AppLayout>
        <div class="p-fluid">
            <!-- Header -->
            <div class="flex flex-wrap items-center justify-between mb-6 gap-4">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 m-0">Dashboard</h1>
                    <p class="text-gray-500 mt-1">Welcome back, here is your business overview.</p>
                </div>
                <div class="flex gap-2">
                    <Button label="Create New" icon="pi pi-plus" @click="toggleCreateMenu" aria-haspopup="true" aria-controls="create_menu" />
                    <Menu ref="createMenu" id="create_menu" :model="createMenuItems" :popup="true" />
                </div>
            </div>

            <!-- KPI Stats Row -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                <Card v-for="stat in kpiStats" :key="stat.label" class="shadow-sm border-none overflow-hidden hover:shadow-md transition-shadow">
                    <template #content>
                        <div class="flex justify-between items-start">
                            <div>
                                <span class="block text-gray-500 font-semibold mb-2 text-xs uppercase tracking-wider">{{ stat.label }}</span>
                                <div class="text-2xl font-bold text-gray-900">₹{{ abbreviateNumber(stat.value) }}</div>
                                <div class="mt-2">
                                    <Tag :value="stat.subtext" :severity="stat.severity" size="small" rounded />
                                </div>
                            </div>
                            <div :class="['p-3 rounded-xl', stat.bgClass, stat.iconClass]">
                                <i :class="[stat.icon, 'text-2xl']"></i>
                            </div>
                        </div>
                    </template>
                </Card>
            </div>

            <!-- Main Content Grid -->
            <div class="grid grid-cols-12 gap-6">
                <!-- Left Column: Chart -->
                <div class="col-span-12 lg:col-span-8">
                    <Card class="h-full border-none shadow-sm">
                        <template #title>
                            <div class="flex items-center justify-between">
                                <span class="text-lg font-bold">Cashflow (Income vs Expense)</span>
                                <Select v-model="selectedPeriod" :options="periodOptions" optionLabel="label" optionValue="value" class="w-40" size="small" />
                            </div>
                        </template>
                        <template #content>
                            <div class="h-80 w-full">
                                <Line v-if="chartData" :data="chartData" :options="chartOptions" />
                                <div v-else class="h-full flex flex-col items-center justify-center text-gray-400">
                                    <ProgressSpinner style="width: 40px; height: 40px" />
                                    <p class="mt-2 text-sm">Loading analytics...</p>
                                </div>
                            </div>
                        </template>
                    </Card>
                </div>

                <!-- Right Column: Alerts & Side Lists -->
                <div class="col-span-12 lg:col-span-4 space-y-6">
                    <!-- Low Stock Alert -->
                    <Card v-if="lowStockProducts.length > 0" class="border-none shadow-sm overflow-hidden bg-red-50/30">
                        <template #title>
                            <div class="flex items-center gap-2 text-red-700">
                                <i class="pi pi-exclamation-triangle"></i>
                                <span class="text-lg font-bold">Low Stock Alert</span>
                            </div>
                        </template>
                        <template #content>
                            <div class="flex flex-col gap-3">
                                <div v-for="product in lowStockProducts.slice(0, 5)" :key="product.id" class="flex justify-between items-center p-2 bg-white rounded-lg border border-red-100">
                                    <div class="flex flex-col">
                                        <span class="font-bold text-sm text-gray-900 truncate w-32">{{ product.name }}</span>
                                        <span class="text-xs text-gray-400">{{ product.unit }}</span>
                                    </div>
                                    <Tag :value="Number(product.current_stock) + ' Left'" severity="danger" />
                                </div>
                                <Button label="View Inventory" severity="danger" text size="small" icon="pi pi-arrow-right" iconPos="right" @click="router.push('/products')" />
                            </div>
                        </template>
                    </Card>

                    <!-- Counts Grid -->
                    <div class="grid grid-cols-2 gap-4">
                        <Card class="text-center border-none shadow-sm hover:bg-blue-50/50 transition-colors">
                            <template #content>
                                <div class="p-3 bg-blue-100 text-blue-600 rounded-xl inline-flex mb-2">
                                    <i class="pi pi-users text-xl"></i>
                                </div>
                                <div class="text-2xl font-bold">{{ metrics.customers }}</div>
                                <div class="text-xs text-gray-500 font-semibold uppercase">Customers</div>
                            </template>
                        </Card>
                        <Card class="text-center border-none shadow-sm hover:bg-orange-50/50 transition-colors">
                            <template #content>
                                <div class="p-3 bg-orange-100 text-orange-600 rounded-xl inline-flex mb-2">
                                    <i class="pi pi-building text-xl"></i>
                                </div>
                                <div class="text-2xl font-bold">{{ metrics.vendors ?? 0 }}</div>
                                <div class="text-xs text-gray-500 font-semibold uppercase">Vendors</div>
                            </template>
                        </Card>
                    </div>

                    <!-- Recent Purchase Invoices -->
                    <Card class="border-none shadow-sm">
                        <template #title>
                            <div class="flex items-center justify-between">
                                <span class="text-lg font-bold">Recent Purchase Bills</span>
                                <Button label="All" icon="pi pi-arrow-right" iconPos="right" text size="small" @click="router.push('/purchases')" />
                            </div>
                        </template>
                        <template #content>
                            <div class="flex flex-col gap-1">
                                <div v-if="recentPurchases.length > 0" class="divide-y">
                                    <div v-for="inv in recentPurchases.slice(0, 5)" :key="inv.id" 
                                        class="py-3 flex items-center justify-between hover:bg-gray-50 cursor-pointer transition-colors px-1"
                                        @click="router.push(`/purchases/${inv.id}/edit`)">
                                        <div class="flex items-center gap-3">
                                            <Avatar :label="inv.vendor_name.substring(0, 1).toUpperCase()" shape="circle" class="bg-orange-100 text-orange-600 font-bold" />
                                            <div class="flex flex-col">
                                                <span class="font-bold text-sm text-gray-900 truncate w-24">{{ inv.vendor_name }}</span>
                                                <span class="text-xs text-gray-500">{{ inv.invoice_number }}</span>
                                            </div>
                                        </div>
                                        <div class="text-right flex flex-col items-end gap-1">
                                            <span class="font-bold text-sm">₹{{ abbreviateNumber(Number(inv.amount)) }}</span>
                                            <Tag :value="inv.status" :severity="getStatusSeverity(inv.status)" size="small" />
                                        </div>
                                    </div>
                                </div>
                                <div v-else class="text-center py-6 text-gray-400 italic">
                                    No purchase bills yet.
                                </div>
                            </div>
                        </template>
                    </Card>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import { useRouter } from 'vue-router'
import AppLayout from '../layouts/AppLayout.vue'
import client from '../api/client'

// PrimeVue
import Button from 'primevue/button'
import Menu from 'primevue/menu'
import Card from 'primevue/card'
import Tag from 'primevue/tag'
import Select from 'primevue/select'
import Avatar from 'primevue/avatar'
import ProgressSpinner from 'primevue/progressspinner'

// Charts
import {
  Chart as ChartJS,
  CategoryScale,
  LinearScale,
  PointElement,
  LineElement,
  Title,
  Tooltip,
  Legend,
  Filler
} from 'chart.js'
import { Line } from 'vue-chartjs'

ChartJS.register(
  CategoryScale,
  LinearScale,
  PointElement,
  LineElement,
  Title,
  Tooltip,
  Legend,
  Filler
)

const router = useRouter()
const createMenu = ref()
const selectedPeriod = ref('6m')

const periodOptions = [
    { label: 'Last 6 Months', value: '6m' },
    { label: 'This Year', value: 'y' }
]

const toggleCreateMenu = (event: any) => {
    createMenu.value.toggle(event)
}

const createMenuItems = [
    { label: 'Sales & Billing', items: [
        { label: 'Standard Invoice', icon: 'pi pi-file-plus', command: () => router.push('/invoices/create') },
        { label: 'Quotation', icon: 'pi pi-file', command: () => router.push('/quotations/create') },
        { label: 'Rough Bill (Kaccha)', icon: 'pi pi-pencil', command: () => router.push('/quick-note') }
    ]},
    { label: 'Purchases', items: [
        { label: 'Record Purchase', icon: 'pi pi-shopping-cart', command: () => router.push('/purchases/create') },
        { label: 'Payment Entry', icon: 'pi pi-wallet', command: () => router.push('/cashbook') }
    ]},
    { label: 'Records', items: [
        { label: 'Add Customer', icon: 'pi pi-user-plus', command: () => router.push('/customers/create') },
        { label: 'Add Product', icon: 'pi pi-box', command: () => router.push('/products/create') }
    ]}
]

interface DashboardMetrics {
  revenue: number
  outstanding: number
  customers: number
  vendors: number
  products: number
  total_expenses: number
  total_purchases: number
  payable_to_vendors: number
}

const metrics = ref<DashboardMetrics>({
  revenue: 0,
  outstanding: 0,
  customers: 0,
  vendors: 0,
  products: 0,
  total_expenses: 0,
  total_purchases: 0,
  payable_to_vendors: 0,
})

const lowStockProducts = ref<any[]>([])
const recentPurchases = ref<any[]>([])
const cashflowHistory = ref<any[]>([])

const kpiStats = computed(() => [
    { 
        label: 'Sales Revenue', 
        value: metrics.value.revenue, 
        icon: 'pi pi-dollar', 
        bgClass: 'bg-indigo-50', 
        iconClass: 'text-indigo-600',
        severity: 'success',
        subtext: 'Received & Pending'
    },
    { 
        label: 'Outstanding', 
        value: metrics.value.outstanding, 
        icon: 'pi pi-clock', 
        bgClass: 'bg-amber-50', 
        iconClass: 'text-amber-600',
        severity: 'warn',
        subtext: 'To be collected'
    },
    { 
        label: 'Total Purchases', 
        value: metrics.value.total_purchases, 
        icon: 'pi pi-shopping-cart', 
        bgClass: 'bg-orange-50', 
        iconClass: 'text-orange-600',
        severity: 'info',
        subtext: 'Vendor bills'
    },
    { 
        label: 'Payable to Vendors', 
        value: metrics.value.payable_to_vendors, 
        icon: 'pi pi-wallet', 
        bgClass: 'bg-red-50', 
        iconClass: 'text-red-600',
        severity: 'danger',
        subtext: 'Pending payment'
    }
])

const chartData = computed(() => {
  if (!cashflowHistory.value || cashflowHistory.value.length === 0) return null
  return {
    labels: cashflowHistory.value.map((d) => {
      const parts = d.month.split('-')
      const date = new Date(parseInt(parts[0]), parseInt(parts[1]) - 1, 1)
      return date.toLocaleString('default', { month: 'short' })
    }),
    datasets: [
      {
        label: 'Income',
        backgroundColor: 'rgba(16, 185, 129, 0.1)',
        borderColor: '#10b981',
        borderWidth: 3,
        pointBackgroundColor: '#ffffff',
        pointBorderWidth: 2,
        fill: true,
        data: cashflowHistory.value.map(d => Number(d.income || 0)),
        tension: 0.4
      },
      {
        label: 'Expense',
        backgroundColor: 'rgba(239, 68, 68, 0.1)',
        borderColor: '#ef4444',
        borderWidth: 3,
        pointBackgroundColor: '#ffffff',
        pointBorderWidth: 2,
        fill: true,
        data: cashflowHistory.value.map(d => Number(d.expense || 0)),
        tension: 0.4
      }
    ]
  }
})

const chartOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: { position: 'top' as const },
    tooltip: {
      backgroundColor: '#1f2937',
      callbacks: {
        label: (context: any) => ' ₹' + context.parsed.y.toLocaleString('en-IN')
      }
    }
  },
  scales: {
    y: {
      beginAtZero: true,
      ticks: {
        callback: (value: any) => '₹' + abbreviateNumber(value)
      }
    },
    x: { grid: { display: false } }
  }
}

const getStatusSeverity = (status: string) => {
    switch (status) {
        case 'paid': return 'success'
        case 'sent': return 'info'
        case 'draft': return 'secondary'
        default: return 'warn'
    }
}

const abbreviateNumber = (value: number) => {
  return new Intl.NumberFormat('en-IN', {
    maximumSignificantDigits: 3,
    notation: "compact",
    compactDisplay: "short"
  }).format(value);
}

onMounted(async () => {
  try {
    const response = await client.get('/dashboard')
    metrics.value = response.data.metrics
    recentPurchases.value = response.data.recent_purchases || []
    lowStockProducts.value = response.data.low_stock_products || []
    cashflowHistory.value = response.data.cashflow_chart || []
  } catch (e) {
    console.error('Failed to load dashboard data', e)
  }
})
</script>
