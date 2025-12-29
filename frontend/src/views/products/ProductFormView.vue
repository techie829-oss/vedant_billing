<template>
    <AppLayout>

        <div class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">{{ isEditing ? 'Edit Product' : 'Add New Product' }}
                </h1>
            </div>
            <div class="mt-4 flex sm:mt-0 sm:ml-4">
                <button type="button" @click="router.back()"
                    class="inline-flex items-center rounded-lg bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50">Cancel</button>
                <button type="button" @click="saveProduct" :disabled="loading"
                    class="ml-3 inline-flex items-center rounded-lg bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600 disabled:opacity-50">
                    {{ loading ? 'Saving...' : 'Save Product' }}
                </button>
            </div>
        </div>

        <div class="bg-white shadow-sm ring-1 ring-gray-900/5 sm:rounded-xl md:col-span-2">
            <div class="px-4 py-6 sm:p-8">
                <div class="grid grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6">

                    <!-- Basic Info -->
                    <div class="sm:col-span-4">
                        <label class="block text-sm font-medium leading-6 text-gray-900">Product Name</label>
                        <div class="mt-2">
                            <input type="text" v-model="form.name"
                                class="block w-full rounded-md border-0 py-2 px-3.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
                        </div>
                    </div>

                    <div class="sm:col-span-2">
                        <label class="block text-sm font-medium leading-6 text-gray-900">Type</label>
                        <div class="relative mt-2">
                            <select v-model="form.type"
                                class="block w-full appearance-none rounded-md border-0 py-2 pl-3.5 pr-10 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
                                <option value="goods">Goods (Inventory)</option>
                                <option value="service">Service</option>
                            </select>
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3">
                                <svg class="h-5 w-5 text-gray-400" viewBox="0 0 20 20" fill="currentColor"
                                    aria-hidden="true">
                                    <path fill-rule="evenodd"
                                        d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z"
                                        clip-rule="evenodd" />
                                </svg>
                            </div>
                        </div>
                    </div>

                    <div class="sm:col-span-3">
                        <label class="block text-sm font-medium leading-6 text-gray-900">SKU / Item Code</label>
                        <div class="mt-2">
                            <input type="text" v-model="form.sku"
                                class="block w-full rounded-md border-0 py-2 px-3.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
                        </div>
                    </div>

                    <div class="sm:col-span-3">
                        <label class="block text-sm font-medium leading-6 text-gray-900">HSN / SAC Code</label>
                        <div class="mt-2">
                            <input type="text" v-model="form.hsn_code"
                                class="block w-full rounded-md border-0 py-2 px-3.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
                        </div>
                    </div>

                    <!-- Pricing -->
                    <div class="sm:col-span-3">
                        <label class="block text-sm font-medium leading-6 text-gray-900">Sales Price (₹)</label>
                        <div class="mt-2">
                            <input type="number" step="0.01" v-model="form.sale_price"
                                class="block w-full rounded-md border-0 py-2 px-3.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
                        </div>
                    </div>

                    <div class="sm:col-span-3">
                        <label class="block text-sm font-medium leading-6 text-gray-900">Tax Rate (%)</label>
                        <div class="relative mt-2">
                            <select v-model="form.tax_rate"
                                class="block w-full appearance-none rounded-md border-0 py-2 pl-3.5 pr-10 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
                                <option :value="0">0% (Exempt)</option>
                                <option :value="5">5%</option>
                                <option :value="12">12%</option>
                                <option :value="18">18%</option>
                                <option :value="28">28%</option>
                            </select>
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3">
                                <svg class="h-5 w-5 text-gray-400" viewBox="0 0 20 20" fill="currentColor"
                                    aria-hidden="true">
                                    <path fill-rule="evenodd"
                                        d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z"
                                        clip-rule="evenodd" />
                                </svg>
                            </div>
                        </div>
                    </div>

                    <div class="sm:col-span-3">
                        <label class="block text-sm font-medium leading-6 text-gray-900">Purchase Price (₹)</label>
                        <div class="mt-2">
                            <input type="number" step="0.01" v-model="form.purchase_price"
                                class="block w-full rounded-md border-0 py-2 px-3.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
                        </div>
                    </div>

                    <div class="sm:col-span-3">
                        <label class="block text-sm font-medium leading-6 text-gray-900">Unit</label>
                        <div class="relative mt-2">
                            <select v-model="form.unit"
                                class="block w-full appearance-none rounded-md border-0 py-2 pl-3.5 pr-10 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
                                <option value="pcs">Pieces (pcs)</option>
                                <option value="box">Box</option>
                                <option value="kg">Kilogram (kg)</option>
                                <option value="m">Meter (m)</option>
                                <option value="l">Liter (l)</option>
                                <option value="other">Other</option>
                            </select>
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3">
                                <svg class="h-5 w-5 text-gray-400" viewBox="0 0 20 20" fill="currentColor"
                                    aria-hidden="true">
                                    <path fill-rule="evenodd"
                                        d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z"
                                        clip-rule="evenodd" />
                                </svg>
                            </div>
                        </div>
                    </div>

                    <!-- Stock (Only for Goods) -->
                    <div v-if="form.type === 'goods'" class="sm:col-span-3">
                        <label class="block text-sm font-medium leading-6 text-gray-900">Opening/Current
                            Stock</label>
                        <div class="mt-2">
                            <input type="number" step="1" v-model="form.current_stock"
                                class="block w-full rounded-md border-0 py-2 px-3.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
                        </div>
                        <p class="mt-1 text-xs text-gray-500">Initial stock balance.</p>
                    </div>

                    <div class="sm:col-span-3">
                        <label class="block text-sm font-medium leading-6 text-gray-900">Status</label>
                        <div class="relative mt-2">
                            <select v-model="form.status"
                                class="block w-full appearance-none rounded-md border-0 py-2 pl-3.5 pr-10 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3">
                                <svg class="h-5 w-5 text-gray-400" viewBox="0 0 20 20" fill="currentColor"
                                    aria-hidden="true">
                                    <path fill-rule="evenodd"
                                        d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z"
                                        clip-rule="evenodd" />
                                </svg>
                            </div>
                        </div>
                    </div>

                    <div class="col-span-full">
                        <label class="block text-sm font-medium leading-6 text-gray-900">Description</label>
                        <div class="mt-2">
                            <textarea v-model="form.description" rows="3"
                                class="block w-full rounded-md border-0 py-2 px-3.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6"></textarea>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        <div v-if="isEditing" class="mt-6 flex justify-end">
            <button type="button" @click="saveProduct" :disabled="loading"
                class="mr-3 inline-flex items-center rounded-lg bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 disabled:opacity-50">
                {{ loading ? 'Saving...' : 'Save Product' }}
            </button>
            <button type="button" @click="deleteProduct"
                class="text-sm font-semibold text-red-600 hover:text-red-500">Delete Product</button>
        </div>
        <div v-else class="mt-6 flex justify-end">
            <button type="button" @click="saveProduct" :disabled="loading"
                class="inline-flex items-center rounded-lg bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 disabled:opacity-50">
                {{ loading ? 'Saving...' : 'Save Product' }}
            </button>
        </div>

    </AppLayout>
</template>

<script setup lang="ts">
import { ref, onMounted, onUnmounted, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useProductStore, type Product } from '../../stores/product'
import AppLayout from '../../layouts/AppLayout.vue'

const route = useRoute()
const router = useRouter()
const productStore = useProductStore()

const isEditing = computed(() => route.params.id !== undefined)
const loading = ref(false)

const form = ref<Partial<Product>>({
    name: '',
    sku: '',
    type: 'goods',
    sale_price: 0,
    purchase_price: 0,
    tax_rate: 18,
    unit: 'pcs',
    current_stock: 0,
    status: 'active',
    description: '',
    hsn_code: ''
})

onMounted(async () => {
    window.addEventListener('keydown', handleKeydown)
    if (isEditing.value) {
        loading.value = true
        try {
            const product = await productStore.fetchProduct(route.params.id as string)
            if (product) {
                // @ts-ignore
                form.value = { ...product }
                // Ensure specific fields are numbers
                form.value.sale_price = Number(product.sale_price)
                form.value.purchase_price = Number(product.purchase_price)
            }
        } catch (e) {
            console.error(e)
            alert('Failed to load product')
            router.push('/products')
        } finally {
            loading.value = false
        }
    }
})

onUnmounted(() => {
    window.removeEventListener('keydown', handleKeydown)
})

function handleKeydown(e: KeyboardEvent) {
    if ((e.ctrlKey || e.metaKey) && e.key === 's') {
        e.preventDefault()
        saveProduct()
    }
}

const saveProduct = async () => {
    if (!form.value.name) return alert('Name is required')
    if (!form.value.sale_price) return alert('Sales Price is required')

    loading.value = true
    try {
        if (isEditing.value) {
            await productStore.updateProduct(route.params.id as string, form.value)
        } else {
            await productStore.createProduct(form.value)
        }
        router.push('/products')
    } catch (e: any) {
        console.error(e)
        const msg = e.response?.data?.message || 'Failed to save product'
        alert(msg)
    } finally {
        loading.value = false
    }
}

const deleteProduct = async () => {
    if (!confirm('Are you sure you want to delete this product?')) return

    loading.value = true
    try {
        await productStore.deleteProduct(route.params.id as string)
        router.push('/products')
    } catch (e) {
        console.error(e)
        alert('Failed to delete product')
        loading.value = false
    }
}
</script>
