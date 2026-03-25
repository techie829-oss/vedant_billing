<template>
    <AppLayout>

        <div class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">{{ isEditing ? 'Edit Product' : 'Add New Product' }}</h1>
                <p class="mt-1 text-sm text-gray-500">
                    {{ isEditing ? 'Update product details and stock information.' : 'Create a new product or service in your catalog.' }}
                </p>
            </div>
            <div class="mt-4 sm:mt-0">
                <button @click="router.push('/products')"
                    class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm font-semibold text-gray-700 shadow-sm hover:bg-gray-50">
                    Back to Catalog
                </button>
            </div>
        </div>

        <div class="bg-white shadow rounded-xl border border-gray-200 overflow-hidden">
            <div class="p-6 sm:p-8">
                <div class="grid grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6">
                    <!-- Basic Info -->
                    <div class="sm:col-span-4">
                        <label class="block text-sm font-medium leading-6 text-gray-900">Product/Service Name</label>
                        <div class="mt-2">
                            <input type="text" v-model="form.name"
                                class="block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6"
                                placeholder="e.g. Wireless Mouse">
                        </div>
                    </div>

                    <div class="sm:col-span-2">
                        <label class="block text-sm font-medium leading-6 text-gray-900">Type</label>
                        <div class="relative mt-2">
                            <select v-model="form.type"
                                class="block w-full appearance-none rounded-md border-0 py-1.5 pl-3 pr-10 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
                                <option value="goods">Goods (Inventory Tracked)</option>
                                <option value="service">Service (No Inventory)</option>
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
                                class="block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
                        </div>
                    </div>

                    <div class="sm:col-span-3">
                        <label class="block text-sm font-medium leading-6 text-gray-900">HSN / SAC Code</label>
                        <div class="mt-2">
                            <input type="text" v-model="form.hsn_code"
                                class="block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
                        </div>
                    </div>

                    <!-- Pricing -->
                    <div class="sm:col-span-3">
                        <label class="block text-sm font-medium leading-6 text-gray-900">Sales Price ({{ form.unit || 'Base Unit' }})</label>
                        <div class="mt-2">
                            <input type="number" step="any" v-model="form.sale_price"
                                class="block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
                        </div>
                        <div class="mt-2 flex items-center h-5">
                            <input id="is_tax_inclusive" name="is_tax_inclusive" type="checkbox"
                                v-model="form.is_tax_inclusive"
                                class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-600">
                            <label for="is_tax_inclusive"
                                class="ml-2 block text-sm font-medium leading-6 text-gray-900">
                                This price includes GST
                            </label>
                        </div>
                    </div>

                    <div v-if="form.secondary_unit" class="sm:col-span-3">
                        <label class="block text-sm font-medium leading-6 text-gray-900">Sales Price ({{ form.secondary_unit }})</label>
                        <div class="mt-2">
                            <input type="number" step="any" v-model="form.secondary_sale_price"
                                class="block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
                        </div>
                        <p v-if="form.conversion_factor > 0" class="mt-1 text-xs text-gray-500">
                            Effective Price per {{ form.unit }}: ₹{{ (Number(form.secondary_sale_price || 0) / Number(form.conversion_factor)).toFixed(2) }}
                        </p>
                    </div>

                    <div class="sm:col-span-3">
                        <label class="block text-sm font-medium leading-6 text-gray-900">Tax Rate (%)</label>
                        <div class="relative mt-2">
                            <select v-model="form.tax_rate"
                                class="block w-full appearance-none rounded-md border-0 py-1.5 pl-3 pr-10 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
                                <template v-if="!configStore.loading && configStore.data">
                                    <option v-for="(label, rate) in configStore.data.gst_rates" :key="rate"
                                        :value="Number(rate)">
                                        {{ label }}
                                    </option>
                                </template>
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
                        <label class="block text-sm font-medium leading-6 text-gray-900">CESS Rate (%)</label>
                        <div class="mt-2">
                            <input type="number" step="any" v-model="form.cess_rate" placeholder="0"
                                class="block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
                        </div>
                    </div>

                    <div class="sm:col-span-3">
                        <label class="block text-sm font-medium leading-6 text-gray-900">Purchase Price ({{ form.unit || 'Base' }})</label>
                        <div class="mt-2">
                            <input type="number" step="any" v-model="form.purchase_price"
                                class="block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
                        </div>
                    </div>

                    <div v-if="form.secondary_unit" class="sm:col-span-3">
                        <label class="block text-sm font-medium leading-6 text-gray-900">Purchase Price ({{ form.secondary_unit }})</label>
                        <div class="mt-2">
                            <input type="number" step="any" v-model="form.secondary_purchase_price"
                                class="block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
                        </div>
                    </div>

                    <!-- Unit Info -->
                    <div class="sm:col-span-3">
                        <label class="block text-sm font-medium leading-6 text-gray-900">Base Unit</label>
                        <div class="relative mt-2">
                            <select v-model="form.unit"
                                class="block w-full appearance-none rounded-md border-0 py-1.5 pl-3 pr-10 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
                                <template v-if="!configStore.loading && configStore.data">
                                    <option v-for="(label, val) in configStore.data.unit_types" :key="val" :value="val">
                                        {{ label }}
                                    </option>
                                </template>
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
                        <label class="block text-sm font-medium leading-6 text-gray-900">Secondary Unit (Optional)</label>
                        <div class="relative mt-2">
                            <select v-model="form.secondary_unit"
                                class="block w-full appearance-none rounded-md border-0 py-1.5 pl-3 pr-10 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
                                <option :value="undefined">None</option>
                                <template v-if="!configStore.loading && configStore.data">
                                    <option v-for="(label, val) in configStore.data.unit_types" :key="val" :value="val">
                                        {{ label }}
                                    </option>
                                </template>
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

                    <div v-if="form.secondary_unit" class="sm:col-span-3">
                        <label class="block text-sm font-medium leading-6 text-gray-900">Conversion Factor</label>
                        <div class="mt-2">
                            <input type="number" step="any" v-model="form.conversion_factor"
                                class="block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
                        </div>
                        <p class="mt-1 text-xs text-gray-500">How many {{ form.unit || 'Base Units' }} in 1 {{ form.secondary_unit }}? (e.g. 1 Carton = 12 Pieces, then enter 12)</p>
                    </div>

                    <!-- Stock (Only for Goods) -->
                    <div v-if="form.type === 'goods'" class="sm:col-span-3">
                        <label class="block text-sm font-medium leading-6 text-gray-900">Opening/Current
                            Stock</label>
                        <div class="mt-2">
                            <input type="number" step="any" v-model="form.current_stock"
                                class="block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
                        </div>
                        <p class="mt-1 text-xs text-gray-500">Initial stock balance.</p>
                    </div>

                    <div class="sm:col-span-3">
                        <label class="block text-sm font-medium leading-6 text-gray-900">Status</label>
                        <div class="relative mt-2">
                            <select v-model="form.status"
                                class="block w-full appearance-none rounded-md border-0 py-1.5 pl-3 pr-10 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
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
                                class="block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6"></textarea>
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
                class="text-sm font-semibold text-red-600 hover:text-red-500">Delete
                Product</button>
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
import { useConfigStore } from '../../stores/config'
import AppLayout from '../../layouts/AppLayout.vue'

const route = useRoute()
const router = useRouter()
const productStore = useProductStore()
const configStore = useConfigStore()

const isEditing = computed(() => !!route.params.id)
const loading = ref(false)

const form = ref({
    name: '',
    sku: '',
    type: 'goods' as 'goods' | 'service',
    sale_price: 0,
    secondary_sale_price: 0,
    purchase_price: 0,
    secondary_purchase_price: 0,
    tax_rate: 18,
    cess_rate: 0,
    is_tax_inclusive: false,
    unit: 'pcs',
    secondary_unit: undefined as string | undefined,
    conversion_factor: 1,
    current_stock: 0,
    status: 'active' as 'active' | 'inactive',
    description: '',
    hsn_code: ''
})

onMounted(async () => {
    configStore.fetchConfig()

    if (isEditing.value) {
        loading.value = true
        try {
            const product = await productStore.fetchProduct(route.params.id as string)
            if (product) {
                form.value = {
                    ...product,
                    secondary_unit: product.secondary_unit || undefined
                }
                // Ensure specific fields are numbers
                form.value.sale_price = Number(product.sale_price) || 0
                form.value.secondary_sale_price = Number(product.secondary_sale_price) || 0
                form.value.purchase_price = Number(product.purchase_price) || 0
                form.value.secondary_purchase_price = Number(product.secondary_purchase_price) || 0
                form.value.tax_rate = Number(product.tax_rate) || 0
                form.value.cess_rate = Number(product.cess_rate) || 0
                form.value.conversion_factor = Number(product.conversion_factor) || 1
            }
        } catch (e) {
            console.error(e)
            alert('Failed to load product')
            router.push('/products')
        } finally {
            loading.value = false
        }
    }

    window.addEventListener('keydown', handleKeydown)
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

async function saveProduct() {
    if (!form.value.name) {
        alert('Please enter a product name')
        return
    }

    loading.value = true
    try {
        if (isEditing.value) {
            await productStore.updateProduct(route.params.id as string, form.value)
        } else {
            await productStore.createProduct(form.value)
        }
        router.push('/products')
    } catch (e) {
        console.error(e)
        alert('Failed to save product')
    } finally {
        loading.value = false
    }
}

async function deleteProduct() {
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
