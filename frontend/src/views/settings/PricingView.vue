<template>
    <AppLayout>
        <template #title>Billing & Subscription</template>

        <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">

            <!-- Current Subscription Status -->
            <div v-if="currentSubscription && currentSubscription.plan"
                class="bg-white overflow-hidden shadow rounded-lg mb-8">
                <div class="px-4 py-5 sm:p-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">Current Plan</h3>
                    <div class="mt-2 max-w-xl text-sm text-gray-500">
                        <p>You are currently on the <strong>{{ currentSubscription.plan.name }}</strong> plan.</p>
                        <p v-if="currentSubscription.ends_at">
                            <span class="text-indigo-600 font-semibold">Next Bill / Expiry:</span>
                            {{ new Date(currentSubscription.ends_at).toLocaleDateString() }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Billing Toggle -->
            <div class="flex justify-center mb-8">
                <span class="mr-3 text-sm font-medium"
                    :class="!isRecurring ? 'text-gray-900' : 'text-gray-500'">One-time</span>
                <button type="button" @click="isRecurring = !isRecurring"
                    :class="isRecurring ? 'bg-indigo-600' : 'bg-gray-200'"
                    class="relative inline-flex flex-shrink-0 h-6 w-11 border-2 border-transparent rounded-full cursor-pointer transition-colors ease-in-out duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    <span :class="isRecurring ? 'translate-x-5' : 'translate-x-0'"
                        class="pointer-events-none inline-block h-5 w-5 rounded-full bg-white shadow transform ring-0 transition ease-in-out duration-200"></span>
                </button>
                <span class="ml-3 text-sm font-medium"
                    :class="isRecurring ? 'text-gray-900' : 'text-gray-500'">Recurring
                    (Auto-debit)</span>
            </div>

            <!-- Plans Grid -->
            <div class="space-y-12 lg:grid lg:grid-cols-3 lg:gap-x-8 lg:space-y-0 text-center">
                <div v-for="plan in plans" :key="plan.id"
                    class="relative p-8 bg-white border border-gray-200 rounded-2xl shadow-sm flex flex-col">
                    <div class="flex-1">
                        <h3 class="text-xl font-semibold text-gray-900">{{ plan.name }}</h3>
                        <p v-if="plan.description"
                            class="absolute top-0 py-1.5 px-4 bg-indigo-500 text-white transform -translate-y-1/2 rounded-full text-xs font-semibold tracking-wide uppercase">
                            Most Popular
                        </p>
                        <p class="mt-4 flex items-baseline justify-center text-gray-900">
                            <span class="text-5xl font-extrabold tracking-tight">₹{{ plan.price }}</span>
                            <span class="ml-1 text-xl font-semibold">/{{ plan.interval }}</span>
                        </p>
                        <p class="mt-6 text-gray-500">{{ plan.features || 'All features included' }}</p>

                        <!-- Feature List (Mock) -->
                        <ul role="list" class="mt-6 space-y-4">
                            <li v-for="feature in ['Unlimited Invoices', 'Priority Support', 'Advanced Analytics']"
                                :key="feature" class="flex">
                                <svg class="flex-shrink-0 w-6 h-6 text-green-500" xmlns="http://www.w3.org/2000/svg"
                                    fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M5 13l4 4L19 7" />
                                </svg>
                                <span class="ml-3 text-gray-500">{{ feature }}</span>
                            </li>
                        </ul>
                    </div>

                    <button @click="buyPlan(plan)" :disabled="loading"
                        class="mt-8 block w-full py-3 px-6 border border-transparent rounded-md text-center font-medium text-white bg-indigo-600 hover:bg-indigo-700">
                        {{ loading ? 'Processing...' : 'Subscribe Now' }}
                    </button>
                </div>
            </div>

            <!-- Billing History -->
            <div class="mt-16 bg-white shadow overflow-hidden sm:rounded-lg">
                <div class="px-4 py-5 sm:px-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">Billing History</h3>
                    <p class="mt-1 max-w-2xl text-sm text-gray-500">Your recent payments and subscriptions.</p>
                </div>
                <div class="border-t border-gray-200">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Date
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Plan
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Amount</th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Type
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Status</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-for="item in history" :key="item.id">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ new Date(item.created_at).toLocaleDateString() }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    {{ item.plan?.name }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    ₹{{ item.plan?.price }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ item.meta?.type === 'one_time' ? 'One-time' : 'Recurring' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full"
                                        :class="item.status === 'active' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'">
                                        {{ item.status }}
                                    </span>
                                </td>
                            </tr>
                            <tr v-if="history.length === 0">
                                <td colspan="5" class="px-6 py-4 text-center text-sm text-gray-500">No payment history
                                    found.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import AppLayout from '../../layouts/AppLayout.vue'
import client from '../../api/client'

// Hardcoded plans for MVP if backend fetch fails or for speed
const plans = ref([
    { id: 1, name: 'Starter', price: 0, interval: 'month', features: 'Basic Features', description: 'For small businesses' },
    { id: 2, name: 'Pro', price: 999, interval: 'month', features: 'Everything in Starter + Pro', description: 'Most popular choice' },
    { id: 3, name: 'Enterprise', price: 4999, interval: 'year', features: 'All Access', description: 'For large organizations' }
])

const currentSubscription = ref<any>(null)
const loading = ref(false)
const isRecurring = ref(true) // Default to recurring

const loadScript = (src: string) => {
    return new Promise((resolve) => {
        const script = document.createElement('script')
        script.src = src
        script.onload = resolve
        document.body.appendChild(script)
    })
}

const buyPlan = async (plan: any) => {
    if (plan.price === 0) {
        alert("Free plan activated!")
        return
    }

    loading.value = true
    try {
        const paymentType = isRecurring.value ? 'subscription' : 'one_time'

        // 1. Initiate Payment
        const res = await client.post('/subscriptions/initiate-payment', {
            plan_id: plan.id,
            type: paymentType
        })

        const data = res.data
        const options: any = {
            key: import.meta.env.VITE_RAZORPAY_KEY_ID || 'rzp_test_S5CUDLKAqJMVjL',
            name: "Vedant Billing",
            description: `${isRecurring.value ? 'Subscription' : 'Payment'} for ${plan.name}`,
            handler: async function (response: any) {
                // 3. Verify Payment
                const verifyPayload: any = {
                    razorpay_payment_id: response.razorpay_payment_id,
                    razorpay_signature: response.razorpay_signature,
                    plan_id: plan.id
                }

                if (response.razorpay_subscription_id) {
                    verifyPayload.razorpay_subscription_id = response.razorpay_subscription_id
                } else if (response.razorpay_order_id) {
                    verifyPayload.razorpay_order_id = response.razorpay_order_id
                }

                await client.post('/subscriptions/verify-payment', verifyPayload)
                alert('Plan Activated Successfully!')
                fetchCurrentSubscription()
            },
            prefill: {
                name: "Business User",
                email: "user@example.com",
                contact: "9999999999"
            },
            theme: { color: "#4f46e5" }
        };

        if (paymentType === 'subscription') {
            options.subscription_id = data.id // Subscription ID
        } else {
            options.order_id = data.id // Order ID
            options.amount = data.amount
            options.currency = data.currency
        }

        const rzp1 = new (window as any).Razorpay(options);
        rzp1.open();

    } catch (e: any) {
        console.error(e)
        alert('Payment Failed: ' + (e.response?.data?.message || e.message))
    } finally {
        loading.value = false
    }
}

const history = ref<any[]>([])

const fetchCurrentSubscription = async () => {
    try {
        const res = await client.get('/subscriptions')
        currentSubscription.value = res.data

        // Fetch History
        const histRes = await client.get('/subscriptions/history')
        history.value = histRes.data.data
    } catch (e) {
        console.error("Failed to fetch subscription", e)
    }
}

onMounted(async () => {
    await loadScript('https://checkout.razorpay.com/v1/checkout.js')
    fetchCurrentSubscription()
})
</script>
