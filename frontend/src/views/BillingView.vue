<template>
    <AppLayout>
        <div class="p-fluid">
            <!-- Header Section -->
            <div class="flex flex-wrap items-center justify-between mb-8 gap-4">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 m-0">Billing & Subscription</h1>
                    <p class="text-gray-500 mt-1">Manage your subscription, billing details, and invoices.</p>
                </div>
                <div v-if="currentSubscription">
                    <Tag :value="'Status: ' + currentSubscription.status" 
                        :severity="currentSubscription.status === 'active' ? 'success' : 'warn'" 
                        size="large" class="uppercase tracking-wider px-4 py-2" />
                </div>
            </div>

            <!-- Loading State -->
            <div v-if="loading" class="flex flex-col items-center justify-center py-20">
                <ProgressSpinner style="width: 50px; height: 50px" strokeWidth="4" />
                <p class="mt-4 text-gray-500">Loading subscription details...</p>
            </div>

            <!-- Error State -->
            <Message v-else-if="error" severity="error" class="mb-6">{{ error }}</Message>

            <div v-else class="space-y-8">
                <!-- Current Subscription Overview -->
                <Card class="border-none shadow-sm overflow-hidden">
                    <template #title>Current Subscription</template>
                    <template #content>
                        <div v-if="currentSubscription" class="grid grid-cols-1 md:grid-cols-2 gap-8 items-center">
                            <!-- Active Plan Details -->
                            <div v-if="currentSubscription.plan" class="p-2">
                                <div class="flex items-baseline gap-3 mb-2">
                                    <span class="text-4xl font-black" :class="currentSubscription.status === 'active' ? 'text-gray-900' : 'text-red-600'">
                                        {{ currentSubscription.plan.name }}
                                    </span>
                                    <Tag :value="'₹' + currentSubscription.plan.price + ' / ' + currentSubscription.plan.interval" severity="secondary" />
                                </div>
                                <p class="text-sm mb-6" :class="currentSubscription.status === 'active' ? 'text-gray-500' : 'text-red-500 font-bold'">
                                    <template v-if="currentSubscription.status === 'active'">
                                        <i class="pi pi-calendar mr-1"></i>
                                        Auto-renews on {{ new Date(currentSubscription.current_cycle_end).toLocaleDateString('en-IN', { dateStyle: 'long' }) }}
                                    </template>
                                    <template v-else>
                                        <i class="pi pi-exclamation-circle mr-1"></i>
                                        Subscription is {{ currentSubscription.status }}. Renew to regain access.
                                    </template>
                                </p>
                                <Button label="Change Plan" icon="pi pi-sync" text class="p-0 h-auto font-bold" @click="scrollToPlans" />
                            </div>

                            <!-- Missing/Deleted Plan -->
                            <div v-else class="p-4 bg-red-50 rounded-xl border border-red-100">
                                <h3 class="text-xl font-bold text-red-700 mb-2">Plan Not Found</h3>
                                <p class="text-sm text-red-600 mb-4">The plan associated with this subscription seems to be invalid or deleted.</p>
                                <Button label="Select a New Plan" icon="pi pi-arrow-right" @click="scrollToPlans" severity="danger" />
                            </div>

                            <!-- Usage Stats -->
                            <div class="bg-gray-50 p-6 rounded-2xl border border-gray-100">
                                <h4 class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-4">Usage this month</h4>
                                
                                <div v-if="currentSubscription.plan" class="space-y-5">
                                    <div v-for="feature in currentSubscription.plan.features" :key="feature.id">
                                        <div v-if="feature.pivot.limit > 0 && getUsage(feature.slug) !== null" class="space-y-1.5">
                                            <div class="flex justify-between items-end">
                                                <span class="text-sm font-bold text-gray-700">{{ feature.name }}</span>
                                                <span class="text-xs">
                                                    <span class="font-black text-gray-900">{{ getUsage(feature.slug) }}</span>
                                                    <span class="text-gray-400 ml-1">/ {{ feature.pivot.limit }}</span>
                                                </span>
                                            </div>
                                            <ProgressBar :value="Math.min((getUsage(feature.slug) / feature.pivot.limit) * 100, 100)" :showValue="false" style="height: 6px" />
                                        </div>
                                    </div>
                                    <!-- Generic message if unlimited -->
                                    <div v-if="currentSubscription.plan.features && currentSubscription.plan.features.every((f: any) => f.pivot.limit < 0)"
                                        class="flex items-center gap-3 p-3 bg-green-50 text-green-700 rounded-xl border border-green-100">
                                        <i class="pi pi-check-circle text-xl"></i>
                                        <span class="text-sm font-bold">You have unlimited access to all features.</span>
                                    </div>
                                </div>
                                <div v-else class="text-sm text-gray-400 italic flex items-center justify-center py-4">
                                    <i class="pi pi-info-circle mr-2"></i>
                                    Usage data unavailable.
                                </div>
                            </div>
                        </div>
                        <div v-else class="text-center py-12">
                            <i class="pi pi-credit-card text-5xl text-gray-200 mb-4"></i>
                            <p class="text-gray-500 font-medium">You do not have an active subscription.</p>
                            <Button label="View Plans" icon="pi pi-arrow-down" class="mt-4" outlined @click="scrollToPlans" />
                        </div>
                    </template>
                </Card>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    <!-- Payment Method -->
                    <Card class="border-none shadow-sm h-full">
                        <template #title>Payment Method</template>
                        <template #content>
                            <div class="flex items-center justify-between p-4 bg-gray-50 border border-gray-100 rounded-xl">
                                <div class="flex items-center gap-4">
                                    <div class="bg-white p-3 rounded-lg shadow-sm">
                                        <i class="pi pi-credit-card text-2xl text-primary"></i>
                                    </div>
                                    <div>
                                        <p class="text-sm font-black text-gray-900">•••• •••• •••• 4242</p>
                                        <p class="text-xs text-gray-500 font-medium">Expires 12/28</p>
                                    </div>
                                </div>
                                <Button label="Update" icon="pi pi-pencil" size="small" text />
                            </div>
                            <div class="mt-6 p-4 bg-primary-50 rounded-xl border border-primary-100 text-primary flex items-start gap-3">
                                <i class="pi pi-shield text-lg mt-0.5"></i>
                                <p class="text-xs font-medium leading-relaxed">
                                    Your payment details are securely handled by Razorpay. Vedant Billing does not store your card information.
                                </p>
                            </div>
                        </template>
                    </Card>

                    <!-- Pending Payment -->
                    <Card v-if="pendingSubscription && pendingSubscription.plan" class="border-none shadow-sm bg-orange-50/50 border-orange-100">
                        <template #title>
                            <div class="flex items-center gap-2 text-orange-700">
                                <i class="pi pi-clock"></i>
                                <span>Pending Payment</span>
                            </div>
                        </template>
                        <template #content>
                            <p class="text-sm text-orange-800 mb-6 leading-relaxed">
                                You have a pending request for <strong>{{ pendingSubscription.plan.name }}</strong>. 
                                Complete the activation to unlock the full features.
                            </p>
                            <Button label="Pay & Activate Now" icon="pi pi-bolt" severity="warn" class="w-full font-bold" @click="subscribe(pendingSubscription.plan.id)" />
                        </template>
                    </Card>

                    <!-- Recent Invoices -->
                    <Card v-else class="border-none shadow-sm h-full">
                        <template #title>
                            <div class="flex justify-between items-center">
                                <span>Invoices</span>
                                <Button label="View All" icon="pi pi-external-link" text size="small" />
                            </div>
                        </template>
                        <template #content>
                            <div class="space-y-1">
                                <div v-if="currentSubscription?.meta?.payment_history?.length">
                                    <div v-for="(inv, idx) in currentSubscription.meta.payment_history.slice(0, 5)" :key="idx"
                                        class="flex justify-between items-center py-3 border-b border-gray-50 last:border-0 hover:bg-gray-50 transition-colors px-2 rounded-lg cursor-pointer">
                                        <div class="flex items-center gap-3">
                                            <i class="pi pi-file-pdf text-red-500 text-lg"></i>
                                            <div>
                                                <p class="text-sm font-bold text-gray-900">{{ inv.invoice_number }}</p>
                                                <p class="text-[10px] text-gray-400 uppercase font-bold">{{ new Date(inv.date).toLocaleDateString() }}</p>
                                            </div>
                                        </div>
                                        <span class="font-black text-gray-900">₹{{ inv.amount }}</span>
                                    </div>
                                </div>
                                <div v-else class="flex flex-col items-center justify-center py-8 text-gray-400">
                                    <i class="pi pi-folder-open text-3xl mb-2"></i>
                                    <p class="text-xs italic">No invoices generated yet.</p>
                                </div>
                            </div>
                        </template>
                    </Card>
                </div>

                <!-- Available Plans -->
                <div id="available-plans" class="pt-8 border-t border-gray-200">
                    <h2 class="text-2xl font-black text-gray-900 mb-8">Available Plans</h2>

                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                        <Card v-for="plan in plans" :key="plan.id" 
                            class="border-none shadow-sm overflow-hidden flex flex-col hover:shadow-lg transition-all duration-300 relative"
                            :class="{ 'ring-2 ring-primary': isCurrentPlan(plan.id) }">
                            <template #header>
                                <div v-if="isCurrentPlan(plan.id)" 
                                    class="absolute top-0 right-0 bg-primary text-white text-[10px] font-black px-4 py-1.5 rounded-bl-xl uppercase tracking-widest z-10">
                                    Current Plan
                                </div>
                            </template>
                            <template #title>
                                <div class="p-2">
                                    <span class="text-lg font-black text-gray-900 uppercase tracking-tight">{{ plan.name }}</span>
                                    <div class="mt-4 flex items-baseline">
                                        <span class="text-4xl font-black text-primary">₹{{ plan.price }}</span>
                                        <span class="ml-1 text-gray-400 font-bold">/{{ plan.interval }}</span>
                                    </div>
                                    <p class="mt-4 text-xs font-medium text-gray-500 leading-relaxed">{{ getPlanDescription(plan) }}</p>
                                </div>
                            </template>
                            <template #content>
                                <div class="py-4">
                                    <ul class="space-y-4 m-0 p-0 list-none">
                                        <li v-for="feature in plan.features" :key="feature.id" class="flex items-start gap-3">
                                            <i v-if="feature.pivot.limit !== 0" class="pi pi-check-circle text-green-500 mt-0.5"></i>
                                            <i v-else class="pi pi-times-circle text-gray-300 mt-0.5"></i>
                                            
                                            <span class="text-sm font-medium" :class="feature.pivot.limit === 0 ? 'text-gray-400' : 'text-gray-700'">
                                                {{ feature.name }}
                                                <Tag v-if="feature.pivot.limit > 0 && feature.type === 'limit'" :value="feature.pivot.limit" severity="secondary" size="small" class="ml-1" />
                                                <Tag v-else-if="feature.pivot.limit < 0" value="Unlimited" severity="success" size="small" class="ml-1" />
                                            </span>
                                        </li>
                                    </ul>
                                </div>
                            </template>
                            <template #footer>
                                <Button @click="isPendingPlan(plan.id) ? processPayment(plan.id) : subscribe(plan.id)"
                                    :disabled="isCurrentPlan(plan.id) || processing"
                                    :label="isCurrentPlan(plan.id) ? 'Active Plan' : (isPendingPlan(plan.id) ? 'Complete Payment' : (currentSubscription?.plan ? 'Switch Plan' : 'Choose Plan'))"
                                    :icon="processing && targetPlanId === plan.id ? 'pi pi-spin pi-spinner' : (isCurrentPlan(plan.id) ? 'pi pi-check' : 'pi pi-arrow-right')"
                                    :severity="isCurrentPlan(plan.id) ? 'secondary' : (isPendingPlan(plan.id) ? 'warn' : 'primary')"
                                    class="w-full font-bold" />
                            </template>
                        </Card>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modals -->
        <BillingAddressModal :isOpen="showAddressModal" :business="authStore.activeBusiness" @close="showAddressModal = false" @saved="handleAddressSaved" />

        <Dialog v-model:visible="showConfirmModal" header="Confirm Plan Change" modal :style="{ width: '400px' }">
            <div class="flex flex-col items-center text-center p-4">
                <div class="h-16 w-16 bg-primary-50 text-primary rounded-full flex items-center justify-center mb-4">
                    <i class="pi pi-sync text-3xl"></i>
                </div>
                <p class="text-gray-700 leading-relaxed font-medium">
                    Are you sure you want to switch your plan? Your subscription will be updated immediately.
                </p>
            </div>
            <template #footer>
                <Button label="Cancel" text severity="secondary" @click="showConfirmModal = false" />
                <Button label="Confirm & Upgrade" icon="pi pi-check" :loading="processing" @click="confirmSubscription" />
            </template>
        </Dialog>
    </AppLayout>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import AppLayout from '../layouts/AppLayout.vue'
import BillingAddressModal from '../components/BillingAddressModal.vue'
import client from '../api/client'
import { useAuthStore } from '../stores/auth'

// PrimeVue
import Card from 'primevue/card'
import Button from 'primevue/button'
import Tag from 'primevue/tag'
import ProgressBar from 'primevue/progressbar'
import ProgressSpinner from 'primevue/progressspinner'
import Message from 'primevue/message'
import Dialog from 'primevue/dialog'

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
        
        if (subRes.data.active || subRes.data.pending) {
            currentSubscription.value = subRes.data.active
            pendingSubscription.value = subRes.data.pending
        } else {
            currentSubscription.value = subRes.data.id ? subRes.data : null;
        }

        if (currentSubscription.value && !currentSubscription.value.plan) {
            currentSubscription.value = null;
            setTimeout(() => { scrollToPlans() }, 500)
        }
    } catch (err: any) {
        error.value = 'Failed to load plans or subscription.'
        console.error(err)
    } finally {
        loading.value = false
    }
}

const isPendingPlan = (planId: string) => pendingSubscription.value && pendingSubscription.value.plan_id === planId
const isCurrentPlan = (planId: string) => currentSubscription.value && currentSubscription.value.plan_id === planId
const getUsage = (featureSlug: string) => {
    if (!currentSubscription.value || !currentSubscription.value.usage) return null;
    return currentSubscription.value.usage[featureSlug] ?? null;
}

const subscribe = async (planId: string) => {
    const business = authStore.activeBusiness
    if (!business) return
    const meta = (business.meta && typeof business.meta === 'object' && !Array.isArray(business.meta)) ? business.meta : {};
    const hasAddress = business.address && meta.city && meta.state && meta.pincode

    if (!hasAddress) {
        targetPlanId.value = planId
        showAddressModal.value = true
        return
    }
    targetPlanId.value = planId
    showConfirmModal.value = true
}

const loadRazorpay = (): Promise<boolean> => {
    return new Promise((resolve) => {
        if ((window as any).Razorpay) { resolve(true); return; }
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
        await client.post('/subscriptions', { plan_id: targetPlanId.value })
        await fetchPlans()
        showConfirmModal.value = false
        const plan = plans.value.find(p => p.id === targetPlanId.value)
        if (plan && plan.price === 0) {
            await client.post('/subscriptions/activate-free', { plan_id: targetPlanId.value })
            await fetchPlans()
            alert('Free plan activated successfully!')
        } else {
            await processPayment(targetPlanId.value)
        }
    } catch (err: any) {
        alert('Failed to select plan. Please try again.')
    } finally {
        processing.value = false
        targetPlanId.value = null
    }
}

const processPayment = async (planId: string) => {
    processing.value = true
    try {
        const isLoaded = await loadRazorpay()
        if (!isLoaded) {
            alert('Failed to load payment gateway.')
            return
        }
        const plan = plans.value.find(p => p.id === planId)
        const paymentType = plan?.razorpay_plan_id ? 'subscription' : 'one_time'
        const initResponse = await client.post('/subscriptions/initiate-payment', { plan_id: planId, type: paymentType })
        const { id: razorpayId, key_id: keyId, notes } = initResponse.data

        const options = {
            key: keyId,
            name: "Vedant Billing",
            description: "Subscription Upgrade",
            [notes && notes.type === 'one_time' ? 'order_id' : 'subscription_id']: razorpayId,
            handler: async (response: any) => { await verifyPayment(response, planId) },
            prefill: { name: authStore.activeBusiness?.name || '', email: authStore.user?.email || '', contact: authStore.activeBusiness?.phone || '' },
            theme: { color: "#4f46e5" },
            modal: { ondismiss: () => { processing.value = false; } }
        }
        const rzp = new (window as any).Razorpay(options)
        rzp.open()
    } catch (err: any) {
        alert(err.response?.data?.message || 'Failed to initiate payment.')
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
        const verResponse = response.data.subscription ? response.data : { subscription: response.data }
        currentSubscription.value = verResponse.subscription || verResponse
        pendingSubscription.value = null
        alert('Payment successful! Plan switched.')
    } catch (err: any) {
        alert('Payment verification failed.')
    } finally {
        processing.value = false
    }
}

const handleAddressSaved = () => {
    showAddressModal.value = false
    if (targetPlanId.value) showConfirmModal.value = true
}

const scrollToPlans = () => {
    document.getElementById('available-plans')?.scrollIntoView({ behavior: 'smooth' });
}

const getPlanDescription = (plan: any) => plan.description || 'Perfect for growing businesses.'

onMounted(() => { fetchPlans() })
</script>
