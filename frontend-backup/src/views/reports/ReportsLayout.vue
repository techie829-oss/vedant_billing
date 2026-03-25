<template>
    <AppLayout>
        <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between">
            <h1 class="text-2xl font-bold text-gray-900">Reports</h1>
        </div>

        <!-- Tabs -->
        <div class="border-b border-gray-200 mb-8 overflow-x-auto">
            <nav class="-mb-px flex space-x-8 min-w-max pb-1" aria-label="Tabs">
                <router-link to="/reports/sales" class="whitespace-nowrap border-b-2 py-4 px-1 text-sm font-medium"
                    :class="[route.path.includes('sales') ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700']">
                    Sales Report
                </router-link>
                <router-link to="/reports/outstanding"
                    class="whitespace-nowrap border-b-2 py-4 px-1 text-sm font-medium"
                    :class="[route.path.includes('outstanding') ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700']">
                    Outstanding (Debtors)
                </router-link>
                <router-link to="/reports/stock" class="whitespace-nowrap border-b-2 py-4 px-1 text-sm font-medium"
                    :class="[route.path.includes('stock') ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700']">
                    Stock Summary
                </router-link>
                <router-link to="/reports/profit-loss"
                    class="whitespace-nowrap border-b-2 py-4 px-1 text-sm font-medium"
                    :class="[route.path.includes('profit-loss') ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700']">
                    Profit & Loss
                </router-link>
                <router-link to="/reports/tax" class="whitespace-nowrap border-b-2 py-4 px-1 text-sm font-medium"
                    :class="[route.path.includes('tax') ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700']">
                    Tax Reports (GST)
                </router-link>
            </nav>
        </div>

        <!-- Report Content -->
        <router-view></router-view>
    </AppLayout>
</template>

<script setup lang="ts">
import { useRoute } from 'vue-router'
import AppLayout from '../../layouts/AppLayout.vue'

const route = useRoute()
</script>
