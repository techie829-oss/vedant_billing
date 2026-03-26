<template>
    <AppLayout>
        <div class="max-w-6xl mx-auto p-fluid">
            <!-- Loading State -->
            <div v-if="loading" class="flex flex-col items-center justify-center py-20">
                <ProgressSpinner style="width: 50px; height: 50px" />
                <p class="mt-4 text-gray-500">Loading your business settings...</p>
            </div>

            <div v-else class="space-y-8">
                <!-- Header Section -->
                <div class="flex flex-wrap items-center justify-between gap-4">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900 m-0">Business Profile</h1>
                        <p class="text-gray-500 mt-1">Manage your company details, branding, and billing preferences.</p>
                    </div>
                    <div class="flex gap-2">
                        <Button label="Save Changes" icon="pi pi-check" :loading="saving" @click="save" />
                    </div>
                </div>

                <div class="grid grid-cols-12 gap-8">
                    <!-- Navigation / Anchor Sidebar -->
                    <div class="hidden lg:block lg:col-span-3">
                        <div class="sticky top-24 space-y-1">
                            <Button v-for="item in navItems" :key="item.id" :label="item.label" :icon="item.icon" 
                                text severity="secondary" class="w-full justify-start font-medium" 
                                :class="{ 'bg-primary-50 text-primary font-bold': activeSection === item.id }"
                                @click="scrollTo(item.id)" />
                        </div>
                    </div>

                    <!-- Main Content -->
                    <div class="col-span-12 lg:col-span-9 space-y-10">
                        
                        <!-- Branding Section -->
                        <section id="branding">
                            <Card class="border-none shadow-sm overflow-hidden">
                                <template #title>Branding & Identity</template>
                                <template #content>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                                        <div class="flex flex-col items-center justify-center p-6 bg-gray-50 rounded-2xl border-2 border-dashed border-gray-200">
                                            <div v-if="form.meta.logo_url" class="relative group">
                                                <Avatar :image="form.meta.logo_url" size="xlarge" shape="circle" class="shadow-lg border-2 border-white w-24 h-24" />
                                                <Button icon="pi pi-trash" severity="danger" rounded class="absolute -top-2 -right-2 opacity-0 group-hover:opacity-100 transition-opacity" @click="removeLogo" />
                                            </div>
                                            <Avatar v-else icon="pi pi-image" size="xlarge" shape="circle" class="bg-gray-200 text-gray-400 w-24 h-24" />
                                            
                                            <div class="mt-4 flex flex-col items-center gap-2">
                                                <label for="logo-upload" class="cursor-pointer">
                                                    <Button label="Upload Logo" icon="pi pi-upload" severity="secondary" outlined size="small" as="span" />
                                                    <input id="logo-upload" type="file" class="hidden" @change="handleLogoUpload" accept="image/*">
                                                </label>
                                                <span class="text-[10px] text-gray-400 uppercase font-bold">Square PNG/JPG preferred</span>
                                            </div>
                                        </div>

                                        <div class="flex flex-col gap-4">
                                            <div class="flex flex-col gap-2">
                                                <label class="font-semibold text-sm">Theme Accent Color</label>
                                                <div class="flex items-center gap-3 p-3 bg-white border rounded-xl shadow-sm">
                                                    <ColorPicker v-model="form.meta.brand_color" />
                                                    <InputText v-model="form.meta.brand_color" class="flex-1 font-mono uppercase" />
                                                </div>
                                                <small class="text-gray-500">Used for your brand identity on invoices.</small>
                                            </div>
                                        </div>
                                    </div>
                                </template>
                            </Card>
                        </section>

                        <!-- Company Details Section -->
                        <section id="details">
                            <Card class="border-none shadow-sm">
                                <template #title>Company Information</template>
                                <template #content>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div class="col-span-1 md:col-span-2 flex flex-col gap-2">
                                            <label class="font-semibold text-sm">GSTIN / Tax ID</label>
                                            <InputGroup>
                                                <InputText v-model="form.gstin" placeholder="Ex. 27AAAC..." />
                                                <Button icon="pi pi-search" severity="secondary" @click="fetchGst" :loading="fetchingGst" />
                                            </InputGroup>
                                        </div>
                                        <div class="flex flex-col gap-2">
                                            <label class="font-semibold text-sm">Business Name *</label>
                                            <InputText v-model="form.name" />
                                        </div>
                                        <div class="flex flex-col gap-2">
                                            <label class="font-semibold text-sm">PAN Number</label>
                                            <InputText v-model="form.pan" />
                                        </div>
                                        <div class="col-span-1 md:col-span-2 flex flex-col gap-2">
                                            <label class="font-semibold text-sm">Address</label>
                                            <Textarea v-model="form.address" rows="3" autoResize />
                                        </div>
                                        <div class="grid grid-cols-3 col-span-1 md:col-span-2 gap-3">
                                            <div class="flex flex-col gap-2">
                                                <label class="font-semibold text-sm">ZIP/PIN</label>
                                                <InputText v-model="form.meta.pincode" />
                                            </div>
                                            <div class="flex flex-col gap-2">
                                                <label class="font-semibold text-sm">City</label>
                                                <InputText v-model="form.meta.city" />
                                            </div>
                                            <div class="flex flex-col gap-2">
                                                <label class="font-semibold text-sm">State</label>
                                                <StateSelect v-model="form.meta.state" />
                                            </div>
                                        </div>
                                        <div class="flex flex-col gap-2">
                                            <label class="font-semibold text-sm">Website</label>
                                            <InputText v-model="form.website" type="url" placeholder="https://..." />
                                        </div>
                                        <div class="flex flex-col gap-2">
                                            <label class="font-semibold text-sm">Currency</label>
                                            <InputText v-model="form.currency" disabled />
                                        </div>
                                    </div>
                                </template>
                            </Card>
                        </section>

                        <!-- Bank Details Section -->
                        <section id="bank">
                            <Card class="border-none shadow-sm">
                                <template #title>Financial & Bank Accounts</template>
                                <template #content>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div class="flex flex-col gap-2">
                                            <label class="font-semibold text-sm">Bank Name</label>
                                            <InputText v-model="form.bank_name" />
                                        </div>
                                        <div class="flex flex-col gap-2">
                                            <label class="font-semibold text-sm">Account Holder Name</label>
                                            <InputText v-model="form.meta.account_holder_name" />
                                        </div>
                                        <div class="flex flex-col gap-2">
                                            <label class="font-semibold text-sm">Account Number</label>
                                            <InputText v-model="form.account_number" />
                                        </div>
                                        <div class="flex flex-col gap-2">
                                            <label class="font-semibold text-sm">IFSC Code</label>
                                            <InputText v-model="form.ifsc_code" />
                                        </div>
                                        <div class="flex flex-col gap-2">
                                            <label class="font-semibold text-sm">UPI ID (for QR Payments)</label>
                                            <InputText v-model="form.meta.upi_id" placeholder="yourname@bank" />
                                        </div>
                                        <div class="flex items-center gap-2 p-3 bg-gray-50 rounded-xl border mt-6">
                                            <Checkbox v-model="form.meta.show_bank_details" :binary="true" id="show_bank" />
                                            <label for="show_bank" class="text-sm font-semibold cursor-pointer">Display Bank Details on Invoices</label>
                                        </div>
                                    </div>
                                </template>
                            </Card>
                        </section>

                        <!-- Invoice Layouts Section -->
                        <section id="layouts">
                            <Card class="border-none shadow-sm overflow-hidden">
                                <template #title>Invoice Design & Layouts</template>
                                <template #content>
                                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                        <div v-for="layout in layoutOptions" :key="layout.id" 
                                            class="relative group border-2 rounded-2xl p-4 cursor-pointer transition-all"
                                            :class="[
                                                form.meta.invoice_layout === layout.id ? 'border-primary ring-1 ring-primary bg-primary-50/10' : 'border-gray-100 bg-gray-50/30 hover:border-gray-300',
                                                !layout.enabled ? 'opacity-60 grayscale cursor-not-allowed' : ''
                                            ]"
                                            @click="layout.enabled ? form.meta.invoice_layout = layout.id : null">
                                            
                                            <div v-if="!layout.enabled" class="absolute inset-0 z-10 flex items-center justify-center bg-white/20 backdrop-blur-[1px]">
                                                <Tag :value="layout.tier" severity="danger" class="shadow-lg" />
                                            </div>

                                            <div class="flex justify-between items-start mb-3">
                                                <span class="font-bold text-gray-900 text-sm">{{ layout.label }}</span>
                                                <i :class="form.meta.invoice_layout === layout.id ? 'pi pi-check-circle text-primary' : 'pi pi-circle text-gray-300'"></i>
                                            </div>
                                            
                                            <div class="h-24 bg-white border border-gray-100 rounded-lg flex items-center justify-center mb-3">
                                                <i :class="layout.icon" class="text-3xl text-gray-300"></i>
                                            </div>

                                            <Button label="Preview" icon="pi pi-eye" severity="secondary" text size="small" class="w-full" @click.stop="openPreview(layout.id)" />
                                        </div>
                                    </div>
                                </template>
                            </Card>
                        </section>

                        <!-- Data Management Section -->
                        <section id="data">
                            <Card class="border-none shadow-sm">
                                <template #title>Data Portability & Import</template>
                                <template #content>
                                    <div class="space-y-8">
                                        <div class="flex flex-col gap-4 p-4 bg-primary-50 rounded-2xl border border-primary-100">
                                            <div class="flex flex-col">
                                                <span class="font-bold text-gray-900">Migrate from Tally</span>
                                                <span class="text-xs text-gray-500">Upload Masters (Ledgers & Items) XML to sync your catalog.</span>
                                            </div>
                                            <div class="flex gap-2">
                                                <input type="file" ref="fileInput" accept=".xml" @change="handleFileSelect" class="hidden">
                                                <Button label="Select XML File" icon="pi pi-file-import" severity="secondary" @click="fileInput?.click()" />
                                                <Button label="Start Import" icon="pi pi-play" :disabled="!selectedFile" :loading="importing" @click="uploadTallyXML" />
                                            </div>
                                            <Message v-if="importStats" severity="success" size="small">
                                                Ledgers: {{ importStats.ledgers_created }} created, Items: {{ importStats.stock_items_created }} created.
                                            </Message>
                                        </div>

                                        <div class="flex flex-col gap-4">
                                            <span class="font-bold text-sm uppercase text-gray-400">Download Backups (CSV)</span>
                                            <div class="flex flex-wrap gap-2">
                                                <Button v-for="type in ['invoices', 'parties', 'expenses']" :key="type" 
                                                    :label="type.toUpperCase()" icon="pi pi-download" severity="secondary" outlined size="small" 
                                                    :loading="downloadingCsv === type" @click="downloadCsv(type)" />
                                            </div>
                                            <Button label="Export Full System JSON Dump" icon="pi pi-database" severity="secondary" outlined @click="downloadData" :loading="downloading" />
                                        </div>
                                    </div>
                                </template>
                            </Card>
                        </section>
                    </div>
                </div>
            </div>
        </div>

        <!-- Live Preview Dialog -->
        <Dialog v-model:visible="showPreviewModal" header="Invoice Layout Preview" :modal="true" :maximized="true" class="p-preview-dialog">
            <div class="flex h-full overflow-hidden">
                <!-- Preview Sidebar -->
                <div class="w-72 border-r bg-white p-6 space-y-6 overflow-y-auto hidden md:block">
                    <div class="space-y-4">
                        <span class="font-bold text-xs uppercase text-gray-400">Toggle Elements</span>
                        <div v-for="opt in previewOptions" :key="opt.id" class="flex items-center gap-2">
                            <Checkbox v-model="previewControls[opt.id]" :binary="true" :id="'prev_'+opt.id" />
                            <label :for="'prev_'+opt.id" class="text-sm cursor-pointer">{{ opt.label }}</label>
                        </div>
                    </div>
                    <div class="space-y-4 pt-6 border-t">
                        <span class="font-bold text-xs uppercase text-gray-400">GST Scenario</span>
                        <div class="flex flex-col gap-2">
                            <div class="flex items-center gap-2">
                                <RadioButton v-model="previewControls.gst_scenario" value="intra" id="gst_intra" />
                                <label for="gst_intra" class="text-sm">Local (CGST+SGST)</label>
                            </div>
                            <div class="flex items-center gap-2">
                                <RadioButton v-model="previewControls.gst_scenario" value="inter" id="gst_inter" />
                                <label for="gst_inter" class="text-sm">Inter-state (IGST)</label>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Preview Canvas -->
                <div class="flex-1 bg-gray-100 p-8 overflow-y-auto flex flex-col items-center">
                    <div class="sticky top-0 z-10 mb-4">
                        <Tag value="REAL-TIME RENDER" severity="info" rounded class="shadow-lg px-4" />
                    </div>
                    <div class="bg-white shadow-2xl origin-top transition-transform duration-300" 
                        :style="{ width: '210mm', minHeight: '297mm' }">
                        <component :is="previewComponent" :invoice="dummyInvoice" :taxBreakdown="dummyTaxBreakdown" :qrCodeUrl="dummyQrCode" />
                    </div>
                </div>
            </div>
        </Dialog>
    </AppLayout>
</template>

<script setup lang="ts">
import { ref, onMounted, computed, reactive } from 'vue'
import AppLayout from '../../layouts/AppLayout.vue'
import client from '../../api/client'
import { useAuthStore } from '../../stores/auth'
import { useGeneralStore } from '../../stores/general'
import StateSelect from '../../components/StateSelect.vue'

// PrimeVue
import Card from 'primevue/card'
import Button from 'primevue/button'
import InputText from 'primevue/inputtext'
import InputGroup from 'primevue/inputgroup'
import Textarea from 'primevue/textarea'
import Checkbox from 'primevue/checkbox'
import ColorPicker from 'primevue/colorpicker'
import Avatar from 'primevue/avatar'
import Dialog from 'primevue/dialog'
import Tag from 'primevue/tag'
import Message from 'primevue/message'
import ProgressSpinner from 'primevue/progressspinner'
import RadioButton from 'primevue/radiobutton'

// Layouts
import DefaultLayout from '../invoices/layouts/DefaultLayout.vue'
import ProfessionalLayout from '../invoices/layouts/ProfessionalLayout.vue'
import GridPremiumLayout from '../invoices/layouts/GridPremiumLayout.vue'
import ClassicGridLayout from '../invoices/layouts/ClassicGridLayout.vue'
import HalfPageLayout from '../invoices/layouts/HalfPageLayout.vue'

const authStore = useAuthStore()
const generalStore = useGeneralStore()

const loading = ref(true)
const saving = ref(false)
const fetchingGst = ref(false)
const importing = ref(false)
const downloading = ref(false)
const downloadingCsv = ref<string | null>(null)
const selectedFile = ref<File | null>(null)
const fileInput = ref<HTMLInputElement | null>(null)
const importStats = ref<any>(null)
const activeSection = ref('branding')

const navItems = [
    { id: 'branding', label: 'Branding', icon: 'pi pi-palette' },
    { id: 'details', label: 'Company Info', icon: 'pi pi-building' },
    { id: 'bank', label: 'Bank Details', icon: 'pi pi-wallet' },
    { id: 'layouts', label: 'Invoices', icon: 'pi pi-file-edit' },
    { id: 'data', label: 'Data Mgmt', icon: 'pi pi-database' }
]

const form = ref({
    name: '', address: '', website: '', currency: 'INR', gstin: '', pan: '', bank_name: '', account_number: '', ifsc_code: '',
    meta: {
        logo_url: '', brand_color: '4F46E5', pincode: '', city: '', state: '', upi_id: '',
        default_notes: '', default_terms: '', account_holder_name: '', show_bank_details: false,
        invoice_layout: 'default' as any
    }
})

const planSlug = computed(() => authStore.currentSubscription?.plan?.slug || 'free')
const layoutOptions = computed(() => [
    { id: 'default', label: 'Minimalist (Free)', icon: 'pi pi-file', enabled: true, tier: 'Free' },
    { id: 'half_page', label: 'Half Page (Paper Saver)', icon: 'pi pi-copy', enabled: ['pro', 'enterprise'].includes(planSlug.value), tier: 'PRO' },
    { id: 'professional', label: 'Professional CRM', icon: 'pi pi-briefcase', enabled: ['enterprise'].includes(planSlug.value), tier: 'ENTERPRISE' },
    { id: 'grid_premium', label: 'Grid Premium', icon: 'pi pi-table', enabled: ['enterprise'].includes(planSlug.value), tier: 'ENTERPRISE' },
    { id: 'classic', label: 'Classic Tax Grid', icon: 'pi pi-th-large', enabled: ['enterprise'].includes(planSlug.value), tier: 'ENTERPRISE' }
])

// Business Logic
const fetchBusiness = async () => {
    loading.value = true
    try {
        await generalStore.fetchStates()
        const res = await client.get(`/businesses/${authStore.activeBusiness?.id}`)
        form.value = { ...res.data, meta: { ...res.data.meta, brand_color: res.data.meta?.brand_color?.replace('#','') || '4F46E5' } }
    } finally { loading.value = false }
}

const save = async () => {
    saving.value = true
    try {
        const payload = { ...form.value, meta: { ...form.value.meta, brand_color: '#' + form.value.meta.brand_color } }
        const res = await client.put(`/businesses/${authStore.activeBusiness?.id}`, payload)
        authStore.setActiveBusiness({ ...res.data, pivot: authStore.activeBusiness?.pivot })
        alert('Settings synced successfully!')
    } finally { saving.value = false }
}

const fetchGst = async () => {
    if (!form.value.gstin) return
    fetchingGst.value = true
    try {
        const res = await client.get(`/gst-lookup/${form.value.gstin}`)
        const d = res.data
        form.value.name = d.legal_name || d.trade_name || form.value.name
        form.value.address = d.address || form.value.address
        if (form.value.gstin.length === 15) form.value.pan = form.value.gstin.substring(2, 12)
    } finally { fetchingGst.value = false }
}

// Preview Logic
const showPreviewModal = ref(false)
const previewLayout = ref('default')
const previewControls = reactive<Record<string, any>>({ show_hsn: true, show_gst_breakdown: true, show_discount: true, show_shipping_address: true, show_eway_details: true, show_qr_bank_details: true, gst_scenario: 'inter' })
const previewOptions = [
    { id: 'show_hsn', label: 'Show HSN/SAC' }, { id: 'show_gst_breakdown', label: 'GST Breakdown' },
    { id: 'show_discount', label: 'Show Discount' }, { id: 'show_shipping_address', label: 'Shipping Address' },
    { id: 'show_eway_details', label: 'Transport Details' }, { id: 'show_qr_bank_details', label: 'Bank & QR' }
]

const openPreview = (id: string) => { previewLayout.value = id; showPreviewModal.value = true }
const previewComponent = computed(() => {
    const layouts: any = { default: DefaultLayout, professional: ProfessionalLayout, grid_premium: GridPremiumLayout, classic: ClassicGridLayout, half_page: HalfPageLayout }
    return layouts[previewLayout.value] || DefaultLayout
})

const dummyInvoice = computed(() => ({
    invoice_number: 'INV-2024-001', date: new Date().toISOString(), grand_total: 6962, subtotal: 6000,
    items: [{ description: 'Sample Product A', hsn_code: '9983', quantity: 1, unit_price: 5000, tax_rate: 18, total: 5900 }],
    party: { name: 'Acme Corp Pvt Ltd', billing_address: { city: 'Mumbai', state: 'Maharashtra' } },
    business: { ...form.value, meta: { ...form.value.meta, brand_color: '#' + form.value.meta.brand_color } },
    meta: { display_options: previewControls }
}))

const dummyTaxBreakdown = computed(() => ({ taxType: previewControls.gst_scenario === 'inter' ? 'IGST' : 'CGST+SGST' }))
const dummyQrCode = 'https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=upi://pay?pa=test@upi'

const handleLogoUpload = async (e: any) => {
    const file = e.target.files[0]; if (!file) return
    const fd = new FormData(); fd.append('file', file)
    const res = await client.post('/upload', fd)
    form.value.meta.logo_url = res.data.url
}
const removeLogo = () => form.value.meta.logo_url = ''

// Tally & Backups
const handleFileSelect = (e: any) => selectedFile.value = e.target.files[0]
const uploadTallyXML = async () => {
    importing.value = true
    try {
        const fd = new FormData(); fd.append('file', selectedFile.value!)
        const res = await client.post('/import/tally', fd)
        importStats.value = res.data.stats
    } finally { importing.value = false; selectedFile.value = null }
}
const downloadCsv = async (t: string) => {
    downloadingCsv.value = t
    try {
        const res = await client.get(`/export/${t}`, { responseType: 'blob' })
        const url = URL.createObjectURL(res.data)
        const a = document.createElement('a'); a.href = url; a.download = `${t}.csv`; a.click()
    } finally { downloadingCsv.value = null }
}
const downloadData = async () => {
    downloading.value = true
    try {
        const res = await client.get('/export')
        const a = document.createElement('a'); a.href = URL.createObjectURL(new Blob([JSON.stringify(res.data)])); a.download = 'backup.json'; a.click()
    } finally { downloading.value = false }
}

const scrollTo = (id: string) => {
    activeSection.value = id
    document.getElementById(id)?.scrollIntoView({ behavior: 'smooth', block: 'start' })
}

onMounted(fetchBusiness)
</script>

<style scoped>
:deep(.p-colorpicker-preview) { border-radius: 8px; border: 1px solid #e5e7eb; }
.p-preview-dialog :deep(.p-dialog-content) { padding: 0; }
</style>
