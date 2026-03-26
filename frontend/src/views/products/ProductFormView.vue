<template>
    <AppLayout>
        <div class="max-w-7xl mx-auto p-fluid">
            <!-- Header Section -->
            <div class="flex flex-wrap items-center justify-between mb-6 gap-4">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 m-0">
                        {{ isEditing ? 'Edit Product' : 'Add New Product' }}
                    </h1>
                    <p class="text-gray-500 mt-1">
                        {{ isEditing ? 'Manage product details and stock.' : 'Create a new catalog item.' }}
                    </p>
                </div>
                <div class="flex gap-2">
                    <Button label="Back to Catalog" icon="pi pi-arrow-left" severity="secondary" outlined 
                        @click="router.push('/products')" />
                    <Button v-if="!isEditing" label="Save Product" icon="pi pi-check" :loading="loading"
                        @click="saveProduct" />
                </div>
            </div>

            <div class="grid grid-cols-12 gap-6">
                <!-- Left Column: Primary Details -->
                <div class="col-span-12 lg:col-span-8 space-y-6">
                    <Card>
                        <template #title>Basic Information</template>
                        <template #content>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="col-span-1 md:col-span-2 flex flex-col gap-2">
                                    <label for="name" class="font-semibold text-sm">Product Name</label>
                                    <InputText id="name" v-model="form.name" placeholder="e.g., Wireless Mouse" />
                                </div>
                                <div class="flex flex-col gap-2">
                                    <label for="sku" class="font-semibold text-sm">SKU / Item Code</label>
                                    <InputText id="sku" v-model="form.sku" placeholder="Optional" />
                                </div>
                                <div class="flex flex-col gap-2">
                                    <label for="type" class="font-semibold text-sm">Type</label>
                                    <Select id="type" v-model="form.type" :options="typeOptions" optionLabel="label" optionValue="value" />
                                </div>
                                <div class="flex flex-col gap-2">
                                    <label for="hsn" class="font-semibold text-sm">HSN / SAC Code</label>
                                    <InputText id="hsn" v-model="form.hsn_code" />
                                </div>
                                <div class="flex flex-col gap-2">
                                    <label for="status" class="font-semibold text-sm">Status</label>
                                    <Select id="status" v-model="form.status" :options="statusOptions" optionLabel="label" optionValue="value" />
                                </div>
                            </div>
                        </template>
                    </Card>

                    <Card>
                        <template #title>Pricing & Tax</template>
                        <template #content>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="flex flex-col gap-2">
                                    <label class="font-semibold text-sm">Sale Price ({{ form.unit }})</label>
                                    <InputNumber v-model="form.sale_price" mode="currency" currency="INR" locale="en-IN" :minFractionDigits="2" />
                                    <div class="flex items-center gap-2 mt-1">
                                        <Checkbox v-model="form.is_tax_inclusive" binary id="tax_incl" />
                                        <label for="tax_incl" class="text-xs">This price includes GST</label>
                                    </div>
                                </div>
                                <div class="flex flex-col gap-2">
                                    <label class="font-semibold text-sm">Tax Rate</label>
                                    <Select v-model="form.tax_rate" :options="gstOptions" optionLabel="label" optionValue="value" placeholder="Select GST" />
                                </div>
                                <div class="flex flex-col gap-2">
                                    <label class="font-semibold text-sm">Purchase Price ({{ form.unit }})</label>
                                    <InputNumber v-model="form.purchase_price" mode="currency" currency="INR" locale="en-IN" :minFractionDigits="2" />
                                </div>
                                <div class="flex flex-col gap-2">
                                    <label class="font-semibold text-sm">CESS Rate (%)</label>
                                    <InputNumber v-model="form.cess_rate" suffix="%" :minFractionDigits="2" />
                                </div>
                            </div>
                        </template>
                    </Card>

                    <Card>
                        <template #title>Units & Conversion</template>
                        <template #content>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="flex flex-col gap-2">
                                    <label class="font-semibold text-sm">Base Unit (Wholesale)</label>
                                    <Select v-model="form.unit" :options="unitOptions" optionLabel="label" optionValue="value" filter />
                                </div>
                                <div class="flex flex-col gap-2">
                                    <label class="font-semibold text-sm">Secondary Unit (Retail)</label>
                                    <Select v-model="form.secondary_unit" :options="unitOptions" optionLabel="label" optionValue="value" filter showClear placeholder="None" />
                                </div>
                                
                                <template v-if="form.secondary_unit">
                                    <div class="flex flex-col gap-2">
                                        <label class="font-semibold text-sm">Conversion Factor</label>
                                        <InputNumber v-model="form.conversion_factor" :minFractionDigits="2" />
                                        <small class="text-gray-500">
                                            1 {{ configStore.data?.unit_types[form.unit] || form.unit }} = 
                                            {{ form.conversion_factor }} {{ configStore.data?.unit_types[form.secondary_unit] || form.secondary_unit }}
                                        </small>
                                    </div>
                                    <div class="flex flex-col gap-2">
                                        <label class="font-semibold text-sm">Secondary Sale Price</label>
                                        <InputNumber v-model="form.secondary_sale_price" mode="currency" currency="INR" locale="en-IN" :minFractionDigits="2" />
                                        <small class="text-gray-500">
                                            Effective: ₹{{ (Number(form.sale_price || 0) / Number(form.conversion_factor || 1)).toFixed(2) }} per {{ form.secondary_unit }}
                                        </small>
                                    </div>
                                </template>
                            </div>
                        </template>
                    </Card>
                </div>

                <!-- Right Column: Secondary Details -->
                <div class="col-span-12 lg:col-span-4 space-y-6">
                    <Card v-if="form.type === 'goods'">
                        <template #title>Inventory</template>
                        <template #content>
                            <div class="flex flex-col gap-4">
                                <div class="flex flex-col gap-2">
                                    <label for="stock" class="font-semibold text-sm">Current/Opening Stock</label>
                                    <InputNumber id="stock" v-model="form.current_stock" :minFractionDigits="2" />
                                    <Message v-if="isEditing" severity="secondary" size="small" variant="simple">Manual edits log as adjustments.</Message>
                                </div>
                            </div>
                        </template>
                    </Card>

                    <Card>
                        <template #title>Description</template>
                        <template #content>
                            <div class="flex flex-col gap-2">
                                <Textarea v-model="form.description" rows="5" autoResize placeholder="Internal notes or product description..." />
                            </div>
                        </template>
                    </Card>

                    <!-- Action Buttons for Editing -->
                    <div v-if="isEditing" class="flex flex-col gap-2 pt-4">
                        <Button label="Save Changes" icon="pi pi-save" :loading="loading" @click="saveProduct" class="w-full" />
                        <Button label="Delete Product" icon="pi pi-trash" severity="danger" text @click="deleteProduct" class="w-full" />
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import { ref, onMounted, onUnmounted, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useProductStore } from '../../stores/product'
import { useConfigStore } from '../../stores/config'
import AppLayout from '../../layouts/AppLayout.vue'
import InputText from 'primevue/inputtext'
import InputNumber from 'primevue/inputnumber'
import Select from 'primevue/select'
import Checkbox from 'primevue/checkbox'
import Textarea from 'primevue/textarea'
import Button from 'primevue/button'
import Card from 'primevue/card'
import Message from 'primevue/message'

const route = useRoute()
const router = useRouter()
const productStore = useProductStore()
const configStore = useConfigStore()

const isEditing = computed(() => !!route.params.id)
const loading = ref(false)

const unitOptions = computed(() => {
    if (!configStore.data?.unit_types) return []
    return Object.entries(configStore.data.unit_types).map(([value, label]) => ({
        label: label as string,
        value: value
    }))
})

const gstOptions = computed(() => {
    if (!configStore.data?.gst_rates) return []
    return Object.entries(configStore.data.gst_rates).map(([rate, label]) => ({
        label: label as string,
        value: Number(rate)
    }))
})

const typeOptions = [
    { label: 'Goods (Inventory Tracked)', value: 'goods' },
    { label: 'Service (No Inventory)', value: 'service' }
]

const statusOptions = [
    { label: 'Active', value: 'active' },
    { label: 'Inactive', value: 'inactive' }
]

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
                    name: product.name || '',
                    sku: product.sku || '',
                    type: product.type as 'goods' | 'service' || 'goods',
                    sale_price: Number(product.sale_price) || 0,
                    secondary_sale_price: Number(product.secondary_sale_price) || 0,
                    purchase_price: Number(product.purchase_price) || 0,
                    secondary_purchase_price: Number(product.secondary_purchase_price) || 0,
                    tax_rate: Number(product.tax_rate) || 0,
                    cess_rate: Number(product.cess_rate) || 0,
                    is_tax_inclusive: !!product.is_tax_inclusive,
                    unit: product.unit || 'pcs',
                    secondary_unit: product.secondary_unit || undefined,
                    conversion_factor: Number(product.conversion_factor) || 1,
                    current_stock: Number(product.current_stock) || 0,
                    status: product.status as 'active' | 'inactive' || 'active',
                    description: product.description || '',
                    hsn_code: product.hsn_code || ''
                }
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
