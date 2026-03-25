<template>
    <AppLayout>
        <div class="p-fluid">
            <!-- Header -->
            <div class="flex flex-wrap items-center justify-between mb-6 gap-4">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 m-0">Business Reports</h1>
                    <p class="text-gray-500 mt-1">Analyze your sales, stock, and financial performance.</p>
                </div>
            </div>

            <!-- Tabs Navigation -->
            <Card class="border-none shadow-sm mb-6">
                <template #content>
                    <Tabs :value="activeTab">
                        <TabList>
                            <Tab v-for="tab in tabItems" :key="tab.route" :value="tab.route" @click="router.push(tab.route)">
                                <i :class="tab.icon" class="mr-2"></i>
                                <span>{{ tab.label }}</span>
                            </Tab>
                        </TabList>
                    </Tabs>
                </template>
            </Card>

            <!-- Report Content -->
            <div class="report-content">
                <router-view></router-view>
            </div>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import AppLayout from '../../layouts/AppLayout.vue'

// PrimeVue Components
import Card from 'primevue/card'
import Tabs from 'primevue/tabs'
import TabList from 'primevue/tablist'
import Tab from 'primevue/tab'

const route = useRoute()
const router = useRouter()

const tabItems = [
    { label: 'Sales Report', icon: 'pi pi-shopping-cart', route: '/reports/sales' },
    { label: 'Outstanding', icon: 'pi pi-users', route: '/reports/outstanding' },
    { label: 'Stock Summary', icon: 'pi pi-box', route: '/reports/stock' },
    { label: 'Profit & Loss', icon: 'pi pi-chart-line', route: '/reports/profit-loss' },
    { label: 'Tax (GST)', icon: 'pi pi-percentage', route: '/reports/tax' }
]

const activeTab = computed(() => {
    const matched = tabItems.find(tab => route.path.startsWith(tab.route))
    return matched ? matched.route : '/reports/sales'
})
</script>

<style scoped>
:deep(.p-tablist-tab-list) {
    border-bottom: none;
}
</style>
