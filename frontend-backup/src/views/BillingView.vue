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
                            <div v-if="currentSubscription.plan.features && currentSubscription.plan.features.every((f: any) => f.pivot.limit < 0)"
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

                <!-- Pending Payment Section -->
                <div v-if="pendingSubscription && pendingSubscription.plan"
                    class="bg-orange-50 rounded-xl shadow-sm border border-orange-200 p-6">
                    <h3 class="text-lg font-semibold text-orange-800 mb-2">Pending Payment</h3>
                    <p class="text-sm text-orange-700 mb-4">
                        You have a pending subscription for <strong>{{ pendingSubscription.plan.name }}</strong>.
                        Please complete the payment to activate it.
                    </p>
                    <button @click="subscribe(pendingSubscription.plan.id)"
                        class="w-full bg-orange-600 text-white rounded-lg py-2 px-4 text-sm font-medium hover:bg-orange-700 transition-colors">
                        Pay & Activate Now
                    </button>
                </div>

                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold text-gray-900">Invoices</h3>
                        <a href="#" class="text-sm text-indigo-600 hover:text-indigo-800">View All</a>
                    </div>
                    <div class="space-y-3">
                        <div
                            v-if="currentSubscription && currentSubscription.meta && currentSubscription.meta.payment_history && currentSubscription.meta.payment_history.length">
                            <div v-for="(inv, idx) in currentSubscription.meta.payment_history.slice(0, 5)" :key="idx"
                                class="flex justify-between items-center text-sm py-2 border-b border-gray-50 last:border-0">
                                <div class="flex items-center">
                                    <svg class="h-4 w-4 text-gray-400 mr-2" fill="none" viewBox="0 0 24 24"
                                        stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                    <span>{{ inv.invoice_number }}</span>
                                </div>
                                <span class="text-gray-500">{{ new Date(inv.date).toLocaleDateString() }}</span>
                                <span class="font-medium text-gray-900">₹{{ inv.amount }}</span>
                            </div>
                        </div>
                        <div v-else class="text-sm text-gray-500 italic py-2">
                            No invoices generated yet.
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
                            <button @click="isPendingPlan(plan.id) ? processPayment(plan.id) : subscribe(plan.id)"
                                :disabled="isCurrentPlan(plan.id) || processing"
                                class="w-full border rounded-lg py-2 px-4 shadow-sm text-sm font-medium focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors"
                                :class="[
                                    isCurrentPlan(plan.id)
                                        ? 'bg-gray-100 text-gray-500 cursor-default border-gray-200'
                                        : isPendingPlan(plan.id)
                                            ? 'bg-orange-600 text-white hover:bg-orange-700 border-transparent'
                                            : 'bg-indigo-600 text-white hover:bg-indigo-700 border-transparent'
                                ]">
                                <span v-if="processing && targetPlanId === plan.id">Processing...</span>
                                <span v-else-if="isCurrentPlan(plan.id)">Current Plan</span>
                                <span v-else-if="isPendingPlan(plan.id)">Complete Payment</span>
                                <span v-else>{{ currentSubscription && currentSubscription.plan ? 'Switch Plan' :
                                    'Choose Plan' }}</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Address Required Modal -->
        <BillingAddressModal :isOpen="showAddressModal" :business="authStore.activeBusiness"
            @close="showAddressModal = false" @saved="handleAddressSaved" />

        <!-- Confirmation Modal -->
        <ConfirmationModal :isOpen="showConfirmModal" title="Confirm Plan Change"
            message="Are you sure you want to switch to this plan? Your subscription will be updated immediately."
            confirmText="Yes, Switch Plan" :processing="processing" @close="showConfirmModal = false"
            @confirm="confirmSubscription" />
    </AppLayout>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import AppLayout from '../layouts/AppLayout.vue'
import BillingAddressModal from '../components/BillingAddressModal.vue'
import ConfirmationModal from '../components/ConfirmationModal.vue'
import client from '../api/client'
import { useAuthStore } from '../stores/auth'

const authStore = useAuthStore()
const plans = ref<any[]>([])
const currentSubscription = ref<any>(null)
const pendingSubscription = ref<any>(null)
const loading = ref(true)
const error = ref<string | null>(null)
const processing = ref(false)
const targetPlanId = ref<string | null>(null)
const showAddressModal = ref(false)
const showConfirmModal = ref(false)

const fetchPlans = async () => {
    try {
        const [plansRes, subRes] = await Promise.all([
            client.get('/plans'),
            client.get('/subscriptions')
        ])
        plans.value = plansRes.data

        // Handle new response format { active: ..., pending: ... }
        if (subRes.data.active || subRes.data.pending) {
            currentSubscription.value = subRes.data.active
            pendingSubscription.value = subRes.data.pending
        } else {
            // Fallback for old format if backend not fully synced/cached (though we updated it)
            currentSubscription.value = subRes.data.id ? subRes.data : null;
        }

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

const isPendingPlan = (planId: string) => {
    return pendingSubscription.value && pendingSubscription.value.plan_id === planId
}

const isCurrentPlan = (planId: string) => {
    return currentSubscription.value && currentSubscription.value.plan_id === planId
}

const getUsage = (featureSlug: string) => {
    if (!currentSubscription.value || !currentSubscription.value.usage) return null;
    return currentSubscription.value.usage[featureSlug] ?? null;
}

const subscribe = async (planId: string) => {
    // 1. Check if business has billing address
    const business = authStore.activeBusiness
    if (!business) return

    // Check for required billing fields: Address, City, State, Pincode
    // Robust check: Handle empty array [] from PHP for meta JSON column
    const meta = (business.meta && typeof business.meta === 'object' && !Array.isArray(business.meta)) ? business.meta : {};

    const hasAddress = business.address &&
        meta.city &&
        meta.state &&
        meta.pincode

    if (!hasAddress) {
        targetPlanId.value = planId
        showAddressModal.value = true
        return
    }

    // Open confirmation modal
    targetPlanId.value = planId
    showConfirmModal.value = true
}

// Helper to load Razorpay script dynamically
const loadRazorpay = (): Promise<boolean> => {
    return new Promise((resolve) => {
        if ((window as any).Razorpay) {
            resolve(true)
            return
        }
        const script = document.createElement('script')
        script.src = 'https://checkout.razorpay.com/v1/checkout.js'
        script.onload = () => resolve(true)
        script.onerror = () => resolve(false)
        document.body.appendChild(script)
    })
}

const confirmSubscription = async () => {
    if (!targetPlanId.value) return;

    processing.value = true
    try {
        // 1. Create Pending Subscription (Database Record)
        await client.post('/subscriptions', { plan_id: targetPlanId.value })

        // 2. Refresh State to show "Pending" UI
        await fetchPlans()

        showConfirmModal.value = false

        // 3. Check if plan is free (₹0) - skip payment
        const plan = plans.value.find(p => p.id === targetPlanId.value)
        if (plan && plan.price === 0) {
            // Free plan - activate immediately without payment
            try {
                await client.post('/subscriptions/activate-free', { plan_id: targetPlanId.value })
                await fetchPlans() // Refresh to show active status
                alert('Free plan activated successfully!')
            } catch (err: any) {
                console.error('Free plan activation error:', err)
                alert('Failed to activate free plan. Please contact support.')
            }
        } else {
            // Paid plan - Initiate Payment immediately
            await processPayment(targetPlanId.value)
        }

    } catch (err: any) {
        console.error('Subscription creation error:', err)
        alert('Failed to select plan. Please try again.')
    } finally {
        processing.value = false
        targetPlanId.value = null
    }
}

const processPayment = async (planId: string) => {
    processing.value = true
    try {
        // 1. Load Razorpay SDK
        const isLoaded = await loadRazorpay()
        if (!isLoaded) {
            alert('Failed to load payment gateway. Please check your internet connection.')
            return
        }

        // 2. Initiate Payment (Get Order/Subscription ID)
        // Uses existing pending subscription from backend
        const plan = plans.value.find(p => p.id === planId)
        const paymentType = plan?.razorpay_plan_id ? 'subscription' : 'one_time'

        const initResponse = await client.post('/subscriptions/initiate-payment', {
            plan_id: planId,
            type: paymentType
        })

        const { id: razorpayId, key_id: keyId, notes } = initResponse.data

        // 3. Open Razorpay Checkout
        const options = {
            key: keyId,
            name: "Vedant Billing",
            description: "Subscription Upgrade",
            [notes && notes.type === 'one_time' ? 'order_id' : 'subscription_id']: razorpayId,
            handler: async (response: any) => {
                await verifyPayment(response, planId)
            },
            prefill: {
                name: authStore.activeBusiness?.name || '',
                email: authStore.user?.email || '',
                contact: authStore.activeBusiness?.phone || ''
            },
            theme: {
                color: "#4f46e5"
            },
            modal: {
                ondismiss: () => {
                    processing.value = false
                    alert('Payment cancelled. You can complete payment explicitly from the dashboard.')
                }
            }
        }

        const rzp = new (window as any).Razorpay(options)
        rzp.open()

    } catch (err: any) {
        console.error('Payment initiation error:', err)
        alert(err.response?.data?.message || 'Failed to initiate payment. Please try again.')
        processing.value = false
    }
}

const verifyPayment = async (paymentResponse: any, planId: string) => {
    try {
        const payload = {
            razorpay_payment_id: paymentResponse.razorpay_payment_id,
            razorpay_signature: paymentResponse.razorpay_signature,
            razorpay_subscription_id: paymentResponse.razorpay_subscription_id,
            razorpay_order_id: paymentResponse.razorpay_order_id,
            plan_id: planId
        }

        const response = await client.post('/subscriptions/verify-payment', payload)

        // Update local state
        const verResponse = response.data.subscription ? response.data : { subscription: response.data }
        currentSubscription.value = verResponse.subscription || verResponse
        pendingSubscription.value = null // Clear pending on success

        alert('Payment successful! Plan switched.')
    } catch (err: any) {
        console.error('Payment verification error:', err)
        alert('Payment verification failed. Please contact support if money was deducted.')
    } finally {
        processing.value = false
    }
}

const handleAddressSaved = async () => {
    showAddressModal.value = false
    if (targetPlanId.value) {
        showConfirmModal.value = true
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
