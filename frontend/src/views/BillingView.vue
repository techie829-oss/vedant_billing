<template>
    <AppLayout>
        <div class="mb-8 flex justify-between items-end">
            <div>
                <h2 class="text-2xl font-bold text-gray-900">Billing & Subscription</h2>
                <p class="text-sm text-gray-500">Manage your subscription, billing details, and invoices.</p>
            </div>
            <div v-if="currentSubscription"
                class="bg-indigo-50 text-indigo-700 px-4 py-2 rounded-lg text-sm font-medium border border-indigo-100">
                Status: <span class="uppercase tracking-wider">{{ currentSubscription.status }}</span>
            </div>
        </div>

        <!-- Loading State -->
        <div v-if="loading" class="flex justify-center py-12">
            <svg class="animate-spin h-8 w-8 text-indigo-600" xmlns="http://www.w3.org/2000/svg" fill="none"
                viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor"
                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                </path>
            </svg>
        </div>

        <!-- Error State -->
        <div v-else-if="error" class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded relative"
            role="alert">
            <strong class="font-bold">Error!</strong>
            <span class="block sm:inline">{{ error }}</span>
        </div>

        <div v-else class="space-y-8">
            <!-- Overview Section -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Current Subscription</h3>

                <div v-if="currentSubscription" class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <!-- Active Plan Details -->
                    <div v-if="currentSubscription.plan">
                        <div class="flex items-center space-x-3 mb-2">
                            <span class="text-3xl font-bold"
                                :class="currentSubscription.status === 'active' ? 'text-gray-900' : 'text-red-600'">
                                {{ currentSubscription.plan.name }}
                            </span>
                            <span class="bg-gray-100 text-gray-600 px-2 py-0.5 rounded text-xs font-medium">
                                ₹{{ currentSubscription.plan.price }} / {{ currentSubscription.plan.interval }}
                            </span>
                        </div>
                        <p class="text-sm mb-6"
                            :class="currentSubscription.status === 'active' ? 'text-gray-500' : 'text-red-500 font-medium'">
                            <span v-if="currentSubscription.status === 'active'">
                                Auto-renews on {{ new Date(currentSubscription.current_cycle_end).toLocaleDateString()
                                }}
                            </span>
                            <span v-else>
                                Subscription is {{ currentSubscription.status }}. Renew to regain access.
                            </span>
                        </p>
                        <button @click="scrollToPlans"
                            class="text-indigo-600 hover:text-indigo-800 text-sm font-medium">
                            Change Plan &rarr;
                        </button>
                    </div>
                    <!-- Missing/Deleted Plan -->
                    <div v-else class="text-red-600">
                        <h3 class="text-xl font-bold mb-2">Plan Not Found</h3>
                        <p class="text-sm mb-4">The plan associated with this subscription seems to be invalid or
                            deleted.</p>
                        <button @click="scrollToPlans"
                            class="text-indigo-600 hover:text-indigo-800 text-sm font-medium">
                            Select a New Plan &rarr;
                        </button>
                    </div>

                    <!-- Usage Stats -->
                    <div class="space-y-4 border-l border-gray-100 pl-8 md:pl-8 sm:border-l-0 sm:pl-0">
                        <h4 class="text-sm font-medium text-gray-500 uppercase tracking-wide mb-3">Usage this month</h4>

                        <div v-if="currentSubscription.plan">
                            <div v-for="feature in currentSubscription.plan.features" :key="feature.id">
                                <!-- Only show limited features that have usage tracking -->
                                <div v-if="feature.pivot.limit > 0 && getUsage(feature.slug) !== null">
                                    <div class="flex justify-between text-xs text-gray-700 mb-1">
                                        <span>{{ feature.name }}</span>
                                        <span>
                                            <span class="font-medium">{{ getUsage(feature.slug) }}</span>
                                            <span class="text-gray-400">/ {{ feature.pivot.limit }}</span>
                                        </span>
                                    </div>
                                    <div class="w-full bg-gray-100 rounded-full h-2">
                                        <div class="bg-indigo-600 h-2 rounded-full transition-all duration-500"
                                            :style="{ width: Math.min((getUsage(feature.slug) / feature.pivot.limit) * 100, 100) + '%' }"
                                            :class="{ 'bg-yellow-500': (getUsage(feature.slug) / feature.pivot.limit) > 0.8, 'bg-red-500': (getUsage(feature.slug) / feature.pivot.limit) >= 1 }">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- Generic message if unlimited -->
                            <div v-if="currentSubscription.plan.features.every((f: any) => f.pivot.limit < 0)"
                                class="text-sm text-green-600 flex items-center">
                                <svg class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                You have unlimited access to all features.
                            </div>
                        </div>
                        <div v-else class="text-sm text-gray-500 italic">
                            Usage data unavailable.
                        </div>
                    </div>
                </div>
                <div v-else class="text-center py-8 text-gray-500">
                    You do not have an active subscription.
                </div>
            </div>

            <!-- Invoices & Details (Placeholder) -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Payment Method</h3>
                    <div class="flex items-center justify-between p-4 border border-gray-200 rounded-lg">
                        <div class="flex items-center">
                            <div class="bg-gray-100 p-2 rounded">
                                <svg class="h-6 w-6 text-gray-500" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-gray-900">•••• •••• •••• 4242</p>
                                <p class="text-xs text-gray-500">Expires 12/28</p>
                            </div>
                        </div>
                    </div>
                    <button class="mt-4 text-sm text-indigo-600 font-medium hover:text-indigo-800">
                        Update Payment Method
                    </button>
                </div>

                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold text-gray-900">Invoices</h3>
                        <a href="#" class="text-sm text-indigo-600 hover:text-indigo-800">View All</a>
                    </div>
                    <div class="space-y-3">
                        <div class="flex justify-between items-center text-sm py-2 border-b border-gray-50">
                            <div class="flex items-center">
                                <svg class="h-4 w-4 text-gray-400 mr-2" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                <span>INV-00123</span>
                            </div>
                            <span class="text-gray-500">Dec 01, 2025</span>
                            <span class="font-medium text-gray-900">₹{{ currentSubscription && currentSubscription.plan
                                ? currentSubscription.plan.price : '0.00' }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Available Plans -->
            <div id="available-plans" class="pt-8 border-t border-gray-200">
                <h3 class="text-xl font-bold text-gray-900 mb-6">Available Plans</h3>

                <!-- Plans Grid Reused -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                    <div v-for="plan in plans" :key="plan.id"
                        class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-md transition-shadow duration-300 flex flex-col relative"
                        :class="{ 'ring-2 ring-indigo-500': isCurrentPlan(plan.id) }">
                        <div v-if="isCurrentPlan(plan.id)"
                            class="absolute top-0 right-0 bg-indigo-500 text-white text-xs font-bold px-3 py-1 rounded-bl-lg">
                            Current Plan
                        </div>

                        <div class="p-6 flex-1">
                            <h3 class="text-lg font-bold text-gray-900">{{ plan.name }}</h3>
                            <div class="mt-4 flex items-baseline">
                                <span class="text-3xl font-extrabold text-gray-900">₹{{ plan.price }}</span>
                                <span class="ml-1 text-gray-500">/{{ plan.interval }}</span>
                            </div>

                            <p class="mt-4 text-sm text-gray-500">{{ getPlanDescription(plan) }}</p>

                            <ul class="mt-6 space-y-4">
                                <li v-for="feature in plan.features" :key="feature.id" class="flex items-start">
                                    <!-- Included Feature (Limit > 0 or -1) -->
                                    <svg v-if="feature.pivot.limit !== 0" class="flex-shrink-0 h-5 w-5 text-green-500"
                                        xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd"
                                            d="M10 18a8 8 0 100-16 8 8 0 000 16zM6.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4a1 1 0 00-1.414-1.414L9 11.586 6.707 9.293z"
                                            clip-rule="evenodd" />
                                    </svg>
                                    <!-- Excluded Feature (Limit == 0) -->
                                    <svg v-else class="flex-shrink-0 h-5 w-5 text-gray-400"
                                        xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd"
                                            d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                                            clip-rule="evenodd" />
                                    </svg>

                                    <span class="ml-3 text-sm"
                                        :class="feature.pivot.limit === 0 ? 'text-gray-400' : 'text-gray-700'">
                                        {{ feature.name }}
                                        <span v-if="feature.pivot.limit > 0 && feature.type === 'limit'"
                                            class="text-gray-500">({{ feature.pivot.limit }})</span>
                                        <span v-else-if="feature.pivot.limit < 0"
                                            class="text-gray-500">(Unlimited)</span>
                                    </span>
                                </li>
                            </ul>
                        </div>

                        <div class="p-6 bg-gray-50 border-t border-gray-100">
                            <button @click="subscribe(plan.id)" :disabled="isCurrentPlan(plan.id) || processing"
                                class="w-full border rounded-lg py-2 px-4 shadow-sm text-sm font-medium focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors"
                                :class="[
                                    isCurrentPlan(plan.id)
                                        ? 'bg-gray-100 text-gray-500 cursor-default border-gray-200'
                                        : 'bg-indigo-600 text-white hover:bg-indigo-700 border-transparent'
                                ]">
                                <span v-if="processing && targetPlanId === plan.id">Processing...</span>
                                <span v-else-if="isCurrentPlan(plan.id)">Current Plan</span>
                                <span v-else>{{ currentSubscription && currentSubscription.plan ? 'Switch Plan' :
                                    'Choose Plan' }}</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import AppLayout from '../layouts/AppLayout.vue'
import client from '../api/client'

const plans = ref<any[]>([])
const currentSubscription = ref<any>(null)
const loading = ref(true)
const error = ref<string | null>(null)
const processing = ref(false)
const targetPlanId = ref<string | null>(null)

const fetchPlans = async () => {
    try {
        const [plansRes, subRes] = await Promise.all([
            client.get('/plans'),
            client.get('/subscriptions')
        ])
        plans.value = plansRes.data
        currentSubscription.value = subRes.data

        // If subscription exists but plan is missing/deleted, treat as no subscription and redirect to plans
        if (currentSubscription.value && !currentSubscription.value.plan) {
            currentSubscription.value = null;
            setTimeout(() => {
                scrollToPlans()
            }, 500)
        }
    } catch (err: any) {
        error.value = 'Failed to load plans or subscription.'
        console.error(err)
    } finally {
        loading.value = false
    }
}

const isCurrentPlan = (planId: string) => {
    return currentSubscription.value && currentSubscription.value.plan_id === planId
}

const getUsage = (featureSlug: string) => {
    if (!currentSubscription.value || !currentSubscription.value.usage) return null;
    return currentSubscription.value.usage[featureSlug] ?? null;
}

const subscribe = async (planId: string) => {
    if (!confirm('Are you sure you want to switch to this plan?')) return;

    processing.value = true
    targetPlanId.value = planId

    try {
        const response = await client.post('/subscriptions', { plan_id: planId })
        currentSubscription.value = response.data
        alert('Plan switched successfully!')
    } catch (err: any) {
        console.error(err)
        alert('Failed to switch plan. Please try again.')
    } finally {
        processing.value = false
        targetPlanId.value = null
    }
}

const scrollToPlans = () => {
    document.getElementById('available-plans')?.scrollIntoView({ behavior: 'smooth' });
}

const getPlanDescription = (plan: any) => {
    return plan.description || 'Perfect for growing businesses.'
}

onMounted(() => {
    fetchPlans()
})
</script>
