<template>
    <AppLayout>
        <div class="space-y-8">
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

            <!-- Quick Links -->
            <div class="mb-8">
                <h3 class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-4 px-1">Quick Access</h3>
                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4">
                    <div v-for="link in quickLinks" :key="link.label" 
                        class="flex flex-col items-center justify-center p-4 bg-white border border-gray-100 rounded-2xl shadow-sm hover:shadow-md hover:border-primary-200 transition-all cursor-pointer group"
                        @click="router.push(link.route)">
                        <div :class="['h-12 w-12 rounded-xl flex items-center justify-center mb-3 group-hover:scale-110 transition-transform duration-300', link.bgClass, link.iconClass]">
                            <i :class="[link.icon, 'text-xl']"></i>
                        </div>
                        <span class="text-sm font-bold text-gray-900 group-hover:text-primary">{{ link.label }}</span>
                        <span class="text-[10px] text-gray-400 mt-1">{{ link.subtext }}</span>
                    </div>
                </div>
            </div>

            <!-- KPI Stats Row -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4 mb-6">
                <Card v-for="stat in kpiStats" :key="stat.label" class="shadow-sm border-none overflow-hidden hover:shadow-md transition-shadow">
                    <template #content>
                        <div class="flex justify-between items-start">
                            <div>
                                <span class="block text-gray-500 font-semibold mb-2 text-[10px] uppercase tracking-wider">{{ stat.label }}</span>
                                <div class="text-xl font-bold text-gray-900">₹{{ abbreviateNumber(stat.value) }}</div>
                                <div class="mt-2">
                                    <Tag :value="stat.subtext" :severity="stat.severity" size="small" rounded class="text-[9px]" />
                                </div>
                            </div>
                            <div :class="['p-2 rounded-lg', stat.bgClass, stat.iconClass]">
                                <i :class="[stat.icon, 'text-xl']"></i>
                            </div>
                        </div>
                    </template>
                </Card>
            </div>

            <!-- Main Content Grid -->
            <div class="grid grid-cols-12 gap-6">
                <!-- Chart Area -->
                <div class="col-span-12 lg:col-span-9">
                    <Card class="border-none shadow-sm h-full">
                        <template #title>
                            <div class="flex items-center justify-between">
                                <div class="flex flex-col">
                                    <span class="text-lg font-bold">Financial Cashflow</span>
                                    <span class="text-xs text-gray-400 font-medium">Monthly payments vs expenses</span>
                                </div>
                                <Select v-model="selectedPeriod" :options="periodOptions" optionLabel="label" optionValue="value" class="w-40" size="small" />
                            </div>
                        </template>
                        <template #content>
                            <div class="h-[400px] w-full pt-4">
                                <Line :data="chartData" :options="chartOptions" />
                            </div>
                        </template>
                    </Card>
                </div>

                <!-- Side Info -->
                <div class="col-span-12 lg:col-span-3 space-y-6">
                    <!-- Business Health -->
                    <Card class="border-none shadow-sm bg-primary-900 text-white overflow-hidden relative">
                        <template #content>
                            <div class="relative z-10">
                                <span class="text-[10px] font-bold uppercase opacity-60">Total Active Stock</span>
                                <div class="text-3xl font-black mt-1">{{ metrics.products }}</div>
                                <p class="text-xs mt-2 opacity-80">Manage your inventory items</p>
                                <Button label="Inventory" icon="pi pi-box" size="small" severity="secondary" class="mt-4 w-full" @click="router.push('/products')" />
                            </div>
                            <i class="pi pi-box absolute -right-4 -bottom-4 text-8xl opacity-10 rotate-12"></i>
                        </template>
                    </Card>

                    <!-- Low Stock Alert -->
                    <Card v-if="lowStockProducts.length > 0" class="border-none shadow-sm overflow-hidden bg-red-50/30">
                        <template #title>
                            <div class="flex items-center gap-2 text-red-700">
                                <i class="pi pi-exclamation-triangle"></i>
                                <span class="text-sm font-bold">Critical Stock</span>
                            </div>
                        </template>
                        <template #content>
                            <div class="flex flex-col gap-2">
                                <div v-for="product in lowStockProducts.slice(0, 3)" :key="product.id" class="flex justify-between items-center p-2 bg-white rounded-lg border border-red-100">
                                    <div class="flex flex-col min-w-0">
                                        <span class="font-bold text-[11px] text-gray-900 truncate w-24">{{ product.name }}</span>
                                        <span class="text-[9px] text-gray-400">{{ product.unit }}</span>
                                    </div>
                                    <Tag :value="Number(product.current_stock)" severity="danger" class="text-[10px]" />
                                </div>
                                <Button label="Restock All" severity="danger" text size="small" class="mt-2 text-xs" @click="router.push('/products')" />
                            </div>
                        </template>
                    </Card>
                </div>
            </div>

            <!-- Secondary Grid: Recent Lists -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <!-- Recent Purchase Invoices -->
                <Card class="border-none shadow-sm">
                    <template #title>
                        <div class="flex items-center justify-between">
                            <span class="text-base font-bold">Recent Purchases</span>
                            <Button label="View All" icon="pi pi-arrow-right" iconPos="right" text size="small" @click="router.push('/purchases')" />
                        </div>
                    </template>
                    <template #content>
                        <div class="flex flex-col gap-1">
                            <div v-if="recentPurchases.length > 0" class="divide-y divide-gray-50">
                                <div v-for="inv in recentPurchases.slice(0, 5)" :key="inv.id" 
                                    class="py-3 flex items-center justify-between hover:bg-gray-50 cursor-pointer transition-colors px-1"
                                    @click="router.push(`/purchases/${inv.id}/edit`)">
                                    <div class="flex items-center gap-3">
                                        <Avatar :label="inv.vendor_name.substring(0, 1).toUpperCase()" shape="circle" class="bg-orange-100 text-orange-600 font-bold" />
                                        <div class="flex flex-col">
                                            <span class="font-bold text-xs text-gray-900 truncate w-32">{{ inv.vendor_name }}</span>
                                            <span class="text-[10px] text-gray-500">{{ inv.invoice_number }}</span>
                                        </div>
                                    </div>
                                    <div class="text-right flex flex-col items-end gap-1">
                                        <span class="font-bold text-xs text-gray-900">₹{{ Number(inv.amount).toLocaleString('en-IN') }}</span>
                                        <Tag :value="inv.status" :severity="getStatusSeverity(inv.status)" class="text-[9px] scale-90" />
                                    </div>
                                </div>
                            </div>
                            <div v-else class="text-center py-6 text-gray-400 italic text-xs">
                                No purchase bills yet.
                            </div>
                        </div>
                    </template>
                </Card>

                <!-- Counts Summary -->
                <div class="flex flex-col gap-6">
                    <div class="grid grid-cols-2 gap-4 h-full">
                        <Card class="text-center border-none shadow-sm hover:bg-blue-50/50 transition-colors flex items-center justify-center">
                            <template #content>
                                <div class="p-3 bg-blue-100 text-blue-600 rounded-xl inline-flex mb-2">
                                    <i class="pi pi-users text-xl"></i>
                                </div>
                                <div class="text-2xl font-bold">{{ metrics.customers }}</div>
                                <div class="text-xs text-gray-500 font-semibold uppercase">Customers</div>
                            </template>
                        </Card>
                        <Card class="text-center border-none shadow-sm hover:bg-orange-50/50 transition-colors flex items-center justify-center">
                            <template #content>
                                <div class="p-3 bg-orange-100 text-orange-600 rounded-xl inline-flex mb-2">
                                    <i class="pi pi-building text-xl"></i>
                                </div>
                                <div class="text-2xl font-bold">{{ metrics.vendors ?? 0 }}</div>
                                <div class="text-xs text-gray-500 font-semibold uppercase">Vendors</div>
                            </template>
                        </Card>
                    </div>
                </div>

                <!-- Additional Info/Quick Action -->
                <Card class="border-none shadow-sm bg-indigo-50 border-indigo-100 flex flex-col justify-center">
                    <template #content>
                        <div class="flex flex-col items-center text-center p-4">
                            <div class="h-16 w-16 bg-white text-indigo-600 rounded-full flex items-center justify-center shadow-sm mb-4">
                                <i class="pi pi-plus text-3xl"></i>
                            </div>
                            <h3 class="text-lg font-bold text-indigo-900 mb-2">Start a New Task</h3>
                            <p class="text-sm text-indigo-700 mb-6">Quickly create an invoice or quotation for your client.</p>
                            <Button label="Create Invoice" icon="pi pi-file-plus" class="w-full" @click="router.push('/invoices/create')" />
                        </div>
                    </template>
                </Card>
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

const kpiStats = computed(() => {
    // Calculate simple growth if possible
    let growthText = 'Stable'
    let growthSeverity = 'info'
    if (cashflowHistory.value.length >= 2) {
        const last = cashflowHistory.value[cashflowHistory.value.length - 1]?.income || 0
        const prev = cashflowHistory.value[cashflowHistory.value.length - 2]?.income || 0
        if (prev > 0) {
            const pct = ((last - prev) / prev) * 100
            growthText = `${pct > 0 ? '+' : ''}${pct.toFixed(0)}% from last month`
            growthSeverity = pct >= 0 ? 'success' : 'warn'
        }
    }

    return [
        { 
            label: 'Sales Revenue', 
            value: metrics.value.revenue, 
            icon: 'pi pi-dollar', 
            bgClass: 'bg-indigo-50', 
            iconClass: 'text-indigo-600',
            severity: growthSeverity,
            subtext: growthText
        },
        { 
            label: 'Outstanding', 
            value: metrics.value.outstanding, 
            icon: 'pi pi-clock', 
            bgClass: 'bg-amber-50', 
            iconClass: 'text-amber-600',
            severity: 'warn',
            subtext: 'Pending collection'
        },
        { 
            label: 'Expenses', 
            value: metrics.value.total_expenses, 
            icon: 'pi pi-receipt', 
            bgClass: 'bg-red-50', 
            iconClass: 'text-red-600',
            severity: 'danger',
            subtext: 'Operational costs'
        },
        { 
            label: 'Purchases', 
            value: metrics.value.total_purchases, 
            icon: 'pi pi-shopping-cart', 
            bgClass: 'bg-orange-50', 
            iconClass: 'text-orange-600',
            severity: 'info',
            subtext: 'Vendor inventory'
        },
        { 
            label: 'Payables', 
            value: metrics.value.payable_to_vendors, 
            icon: 'pi pi-wallet', 
            bgClass: 'bg-teal-50', 
            iconClass: 'text-teal-600',
            severity: 'secondary',
            subtext: 'Due to suppliers'
        }
    ]
})

const quickLinks = [
    { label: 'Invoices', subtext: 'Sales records', icon: 'pi pi-file', route: '/invoices', bgClass: 'bg-indigo-50', iconClass: 'text-indigo-600' },
    { label: 'Purchases', subtext: 'Vendor bills', icon: 'pi pi-shopping-cart', route: '/purchases', bgClass: 'bg-orange-50', iconClass: 'text-orange-600' },
    { label: 'Fast Note', subtext: 'Rough bills', icon: 'pi pi-pencil', route: '/quick-note', bgClass: 'bg-yellow-50', iconClass: 'text-yellow-600' },
    { label: 'Estimates', subtext: 'Draft quotes', icon: 'pi pi-file-edit', route: '/quotations', bgClass: 'bg-teal-50', iconClass: 'text-teal-600' },
    { label: 'Customers', subtext: 'Client list', icon: 'pi pi-users', route: '/customers', bgClass: 'bg-blue-50', iconClass: 'text-blue-600' },
    { label: 'Cashbook', subtext: 'Cash flows', icon: 'pi pi-wallet', route: '/cashbook', bgClass: 'bg-emerald-50', iconClass: 'text-emerald-600' }
]

const chartData = computed(() => {
  // Generate last 6 months labels
  const labels = []
  const incomeValues = []
  const expenseValues = []
  
  const now = new Date()
  for (let i = 5; i >= 0; i--) {
    const d = new Date(now.getFullYear(), now.getMonth() - i, 1)
    const monthKey = d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0')
    const monthLabel = d.toLocaleString('default', { month: 'short', year: '2-digit' })
    
    labels.push(monthLabel)
    
    const record = cashflowHistory.value.find(r => r.month === monthKey)
    incomeValues.push(record ? Number(record.income || 0) : 0)
    expenseValues.push(record ? Number(record.expense || 0) : 0)
  }

  return {
    labels,
    datasets: [
      {
        label: 'Income (Payments Received)',
        backgroundColor: 'rgba(16, 185, 129, 0.1)',
        borderColor: '#10b981',
        borderWidth: 3,
        pointBackgroundColor: '#ffffff',
        pointBorderWidth: 2,
        pointRadius: 4,
        pointHoverRadius: 6,
        fill: true,
        data: incomeValues,
        tension: 0.4
      },
      {
        label: 'Expense (Bills Paid)',
        backgroundColor: 'rgba(239, 68, 68, 0.1)',
        borderColor: '#ef4444',
        borderWidth: 3,
        pointBackgroundColor: '#ffffff',
        pointBorderWidth: 2,
        pointRadius: 4,
        pointHoverRadius: 6,
        fill: true,
        data: expenseValues,
        tension: 0.4
      }
    ]
  }
})

const chartOptions = computed(() => ({
  responsive: true,
  maintainAspectRatio: false,
  interaction: {
    intersect: false,
    mode: 'index'
  },
  plugins: {
    legend: { 
        position: 'top' as const,
        align: 'end' as const,
        labels: {
            boxWidth: 10,
            usePointStyle: true,
            pointStyle: 'circle',
            font: { weight: '600' }
        }
    },
    tooltip: {
      padding: 12,
      backgroundColor: '#1f2937',
      titleFont: { size: 14, weight: 'bold' },
      bodyFont: { size: 13 },
      callbacks: {
        label: (context: any) => ` ${context.dataset.label.split(' (')[0]}: ₹${context.parsed.y.toLocaleString('en-IN')}`
      }
    }
  },
  scales: {
    y: {
      beginAtZero: true,
      grid: {
        color: '#f3f4f6'
      },
      ticks: {
        font: { size: 11 },
        callback: (value: any) => '₹' + abbreviateNumber(value)
      }
    },
    x: { 
      grid: { display: false },
      ticks: {
        font: { size: 11, weight: '600' }
      }
    }
  }
}))

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
