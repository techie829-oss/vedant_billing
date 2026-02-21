<template>
    <AppLayout>
        <!-- Sticky Top Status Bar -->
        <div class="sticky top-0 z-10 bg-white border-b border-gray-200 shadow-sm mb-6">
            <div class="px-4 sm:px-6 lg:px-8 py-4">
                <div class="flex items-center justify-between">
                    <!-- Left: Back + Title + Status -->
                    <div class="flex items-center gap-4">
                        <button @click="$router.back()" type="button"
                            class="inline-flex items-center text-gray-600 hover:text-gray-900">
                            <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                            </svg>
                            Back
                        </button>
                        <div class="flex items-center gap-3">
                            <h2 class="text-xl font-bold text-gray-900">
                                {{ isEditMode ? `${getDocumentTypeLabel()} #${form.invoice_number}` : `New
                                ${getDocumentTypeLabel()}` }}
                            </h2>
                            <span v-if="isEditMode"
                                class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium" :class="{
                                    'bg-gray-100 text-gray-800': !form.status || form.status === 'draft',
                                    'bg-blue-100 text-blue-800': form.status === 'sent',
                                    'bg-green-100 text-green-800': form.status === 'paid'
                                }">
                                {{ form.status ? form.status.toUpperCase() : 'DRAFT' }}
                            </span>
                        </div>
                    </div>
                    <!-- Right: Action Buttons -->
                    <div class="flex gap-2">
                        <button @click="save('draft')" :disabled="loading" type="button"
                            class="inline-flex items-center rounded-lg bg-white px-4 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50">
                            {{ loading ? 'Saving...' : 'Save Draft' }}
                        </button>
                        <button @click="save('sent')" :disabled="loading" type="button"
                            class="inline-flex items-center rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700">
                            {{ loading ? 'Saving...' : 'Save & Finalize' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <form @submit.prevent="save('draft')" class="space-y-6">
            <!-- Display Options -->
            <div class="bg-white shadow-sm ring-1 ring-gray-900/5 sm:rounded-xl">
                <div class="p-3 sm:p-6">
                    <button type="button" @click="showDisplayOptions = !showDisplayOptions"
                        class="flex w-full items-center justify-between text-left">
                        <div>
                            <h3 class="text-base font-semibold leading-7 text-gray-900">Display Options</h3>
                            <p class="mt-1 text-sm text-gray-500">
                                {{ getActiveOptionsCount() }} options active
                            </p>
                        </div>
                        <svg class="h-5 w-5 text-gray-400 transition-transform"
                            :class="{ 'rotate-180': showDisplayOptions }" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>

                    <div v-show="showDisplayOptions" class="mt-4 space-y-4">
                        <!-- Quick Presets -->
                        <div class="flex flex-wrap gap-2">
                            <button type="button" @click="applyPreset('simple')"
                                class="px-3 py-1.5 text-xs font-medium rounded-md bg-gray-100 text-gray-700 hover:bg-gray-200">
                                Simple Invoice
                            </button>
                            <button type="button" @click="applyPreset('gst')"
                                class="px-3 py-1.5 text-xs font-medium rounded-md bg-gray-100 text-gray-700 hover:bg-gray-200">
                                GST Invoice
                            </button>
                            <button type="button" @click="applyPreset('full')"
                                class="px-3 py-1.5 text-xs font-medium rounded-md bg-gray-100 text-gray-700 hover:bg-gray-200">
                                Full Details
                            </button>
                        </div>

                        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 md:grid-cols-3 sm:gap-4">
                            <div class="relative flex items-start">
                                <div class="flex h-6 items-center">
                                    <input id="show_hsn" type="checkbox" v-model="form.meta.display_options.show_hsn"
                                        class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-600">
                                </div>
                                <div class="ml-3 text-sm leading-6">
                                    <label for="show_hsn" class="font-medium text-gray-900">Show HSN/SAC Column</label>
                                </div>
                            </div>
                            <div class="relative flex items-start">
                                <div class="flex h-6 items-center">
                                    <input id="show_gst" type="checkbox"
                                        v-model="form.meta.display_options.show_gst_breakdown"
                                        class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-600">
                                </div>
                                <div class="ml-3 text-sm leading-6">
                                    <label for="show_gst" class="font-medium text-gray-900">Show GST Breakdown</label>
                                </div>
                            </div>
                            <div class="relative flex items-start">
                                <div class="flex h-6 items-center">
                                    <input id="show_discount" type="checkbox"
                                        v-model="form.meta.display_options.show_discount"
                                        class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-600">
                                </div>
                                <div class="ml-3 text-sm leading-6">
                                    <label for="show_discount" class="font-medium text-gray-900">Show Discount
                                        Column</label>
                                </div>
                            </div>
                            <div class="relative flex items-start">
                                <div class="flex h-6 items-center">
                                    <input id="show_qr" type="checkbox"
                                        v-model="form.meta.display_options.show_qr_bank_details"
                                        class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-600">
                                </div>
                                <div class="ml-3 text-sm leading-6">
                                    <label for="show_qr" class="font-medium text-gray-900">Show Bank/QR Details</label>
                                </div>
                            </div>
                            <div class="relative flex items-start">
                                <div class="flex h-6 items-center">
                                    <input id="show_shipping" type="checkbox"
                                        v-model="form.meta.display_options.show_shipping_address"
                                        class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-600">
                                </div>
                                <div class="ml-3 text-sm leading-6">
                                    <label for="show_shipping" class="font-medium text-gray-900">Show Shipping
                                        Address</label>
                                </div>
                            </div>
                            <div class="relative flex items-start">
                                <div class="flex h-6 items-center">
                                    <input id="show_transport" type="checkbox"
                                        v-model="form.meta.display_options.show_transport_details"
                                        class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-600">
                                </div>
                                <div class="ml-3 text-sm leading-6">
                                    <label for="show_transport" class="font-medium text-gray-900">Show Transport/Order
                                        Details</label>
                                </div>
                            </div>
                            <div class="relative flex items-start">
                                <div class="flex h-6 items-center">
                                    <input id="show_description" type="checkbox"
                                        v-model="form.meta.display_options.show_description"
                                        class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-600">
                                </div>
                                <div class="ml-3 text-sm leading-6">
                                    <label for="show_description" class="font-medium text-gray-900">Show
                                        Notes</label>
                                </div>
                            </div>
                        </div>

                        <!-- Remember Settings Toggle -->
                        <div class="border-t pt-4 flex items-center">
                            <input id="remember_settings" type="checkbox" v-model="rememberSettings"
                                class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-600">
                            <label for="remember_settings" class="ml-2 text-sm font-medium text-gray-700">
                                Remember these settings for future invoices
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Document Type Selection -->
            <div class="bg-white shadow-sm ring-1 ring-gray-900/5 sm:rounded-xl">
                <div class="px-4 py-5 sm:p-6">
                    <div class="grid grid-cols-1 gap-x-6 gap-y-6 sm:grid-cols-6">
                        <div class="sm:col-span-3">
                            <CustomSelect id="document-type" label="Document Type" v-model="form.type"
                                :options="documentTypeOptions" />
                        </div>

                        <!-- Copy Type Selection (Only for Bill of Supply) -->
                        <div v-if="form.type === 'bill_of_supply'" class="sm:col-span-3">
                            <label for="copy-type" class="block text-sm font-medium leading-6 text-gray-900">Copy
                                Type</label>
                            <select id="copy-type" v-model="form.meta.copy_type"
                                class="mt-2 block w-full rounded-md border-0 py-2 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
                                <option value="original">Original for Recipient</option>
                                <option value="duplicate">Duplicate for Transporter</option>
                                <option value="triplicate">Triplicate for Supplier</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Customer & Invoice Details Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 items-start">
                <!-- Card 1: Customer & Invoice Details -->
                <div class="bg-white shadow-sm ring-1 ring-gray-900/5 sm:rounded-xl">
                    <div class="px-4 py-5 sm:p-6">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <CustomSelect id="customer" label="Customer" v-model="form.party_id"
                                    :options="customers.map(c => ({ label: c.name, value: c.id, description: c.raw_phone || c.email }))"
                                    searchable />

                                <div class="mt-2 text-right">
                                    <router-link to="/parties/create?type=customer"
                                        class="text-sm text-indigo-600 hover:text-indigo-500">
                                        + Add New Customer
                                    </router-link>
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium leading-6 text-gray-900">Invoice Number</label>
                                <div class="mt-1">
                                    <input type="text" v-model="form.invoice_number" placeholder="(Auto-generated)"
                                        class="block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6" />
                                </div>
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-4 mt-4">
                            <div>
                                <label for="date" class="block text-sm font-medium leading-6 text-gray-900">Invoice
                                    Date</label>
                                <div class="mt-1">
                                    <input type="date" id="date" v-model="form.date" required
                                        class="block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6" />
                                </div>
                            </div>

                            <div>
                                <label for="due_date" class="block text-sm font-medium leading-6 text-gray-900">Due
                                    Date</label>
                                <div class="mt-1">
                                    <input type="date" id="due_date" v-model="form.due_date" required
                                        class="block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6" />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Card 2: Transport / Order Details (Conditional) -->
                <div v-if="form.meta.display_options.show_transport_details"
                    class="bg-white shadow-sm ring-1 ring-gray-900/5 sm:rounded-xl">
                    <div class="px-4 py-5 sm:p-6">
                        <h4 class="text-base font-semibold leading-7 text-gray-900 mb-4">Transport / Order Details</h4>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium leading-6 text-gray-900">Challan No.</label>
                                <input type="text" v-model="form.challan_no"
                                    class="mt-1 block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium leading-6 text-gray-900">Vehicle No.</label>
                                <input type="text" v-model="form.vehicle_no"
                                    class="mt-1 block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium leading-6 text-gray-900">E-Way Bill No.</label>
                                <input type="text" v-model="form.eway_bill_no"
                                    class="mt-1 block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium leading-6 text-gray-900">PO Number</label>
                                <input type="text" v-model="form.po_number"
                                    class="mt-1 block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6" />
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Billing Addresses -->
            <div v-if="form.party_id" class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Billing Address -->
                <div class="bg-white shadow-sm ring-1 ring-gray-900/5 sm:rounded-xl">
                    <div class="px-4 py-5 sm:p-6">
                        <h3 class="text-base font-semibold leading-7 text-gray-900 mb-4">Billing Address</h3>
                        <div class="grid grid-cols-2 gap-x-4 gap-y-3 sm:grid-cols-6 sm:gap-x-6 sm:gap-y-4">
                            <div class="col-span-2 sm:col-span-6">
                                <label class="block text-sm font-medium leading-6 text-gray-900">Street</label>
                                <input type="text" v-model="form.meta.billing_address.street"
                                    class="mt-2 block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6" />
                            </div>
                            <div class="col-span-1 sm:col-span-2">
                                <label class="block text-sm font-medium leading-6 text-gray-900">City</label>
                                <input type="text" v-model="form.meta.billing_address.city"
                                    class="mt-2 block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6" />
                            </div>
                            <div class="col-span-1 sm:col-span-2">
                                <label class="block text-sm font-medium leading-6 text-gray-900">State</label>
                                <div class="relative mt-2">
                                    <StateSelect v-model="form.meta.billing_address.state" />
                                </div>
                            </div>
                            <div class="col-span-1 sm:col-span-2">
                                <label class="block text-sm font-medium leading-6 text-gray-900">ZIP</label>
                                <input type="text" v-model="form.meta.billing_address.zip"
                                    class="mt-2 block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6" />
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Shipping Address -->
                <div v-if="form.meta.display_options.show_shipping_address"
                    class="bg-white shadow-sm ring-1 ring-gray-900/5 sm:rounded-xl">
                    <div class="px-4 py-5 sm:p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-base font-semibold leading-7 text-gray-900">Shipping Address</h3>
                            <div class="flex items-center">
                                <input id="same_as_billing" type="checkbox"
                                    @change="(e: Event) => { if ((e.target as HTMLInputElement).checked) form.meta.shipping_address = { ...form.meta.billing_address } }"
                                    class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-600">
                                <label for="same_as_billing" class="ml-2 text-sm text-gray-600">Same as Billing</label>
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-x-4 gap-y-3 sm:grid-cols-6 sm:gap-x-6 sm:gap-y-4">
                            <div class="col-span-2 sm:col-span-6">
                                <label class="block text-sm font-medium leading-6 text-gray-900">Street</label>
                                <input type="text" v-model="form.meta.shipping_address.street"
                                    class="mt-2 block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6" />
                            </div>
                            <div class="col-span-1 sm:col-span-2">
                                <label class="block text-sm font-medium leading-6 text-gray-900">City</label>
                                <input type="text" v-model="form.meta.shipping_address.city"
                                    class="mt-2 block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6" />
                            </div>
                            <div class="col-span-1 sm:col-span-2">
                                <label class="block text-sm font-medium leading-6 text-gray-900">State</label>
                                <div class="relative mt-2">
                                    <StateSelect v-model="form.meta.shipping_address.state" />
                                </div>
                            </div>
                            <div class="col-span-1 sm:col-span-2">
                                <label class="block text-sm font-medium leading-6 text-gray-900">ZIP</label>
                                <input type="text" v-model="form.meta.shipping_address.zip"
                                    class="mt-2 block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6" />
                            </div>
                        </div>
                    </div>
                </div>
            </div>



            <!-- Line Items -->
            <div class="bg-white shadow-sm ring-1 ring-gray-900/5 sm:rounded-xl">
                <div class="px-4 py-5 sm:p-6">
                    <h3 class="text-base font-semibold leading-7 text-gray-900 mb-4">Items</h3>
                    <!-- Mobile View: Stacked Cards -->
                    <div class="block sm:hidden space-y-4">
                        <div v-for="(item, index) in form.items" :key="index"
                            class="bg-gray-50 rounded-lg p-4 border border-gray-200 relative">

                            <!-- Delete Button (Top Right) -->
                            <button @click="removeItem(index)" type="button"
                                class="absolute top-2 right-2 text-red-600 hover:text-red-900 p-1">
                                <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd"
                                        d="M8.75 1A2.75 2.75 0 006 3.75v.443c-.795.077-1.584.176-2.365.298a.75.75 0 10.23 1.482l.149-.022.841 10.518A2.75 2.75 0 007.596 19h4.807a2.75 2.75 0 002.742-2.53l.841-10.52.149.023a.75.75 0 00.23-1.482A41.03 41.03 0 0014 4.193V3.75A2.75 2.75 0 0011.25 1h-2.5zM10 4c.84 0 1.673.025 2.5.075V3.75c0-.69-.56-1.25-1.25-1.25h-2.5c-.69 0-1.25.56-1.25 1.25v.325C8.327 4.025 9.16 4 10 4zM8.58 7.72a.75.75 0 00-1.5.06l.3 7.5a.75.75 0 101.5-.06l-.3-7.5zm4.34.06a.75.75 0 10-1.5-.06l-.3 7.5a.75.75 0 101.5.06l.3-7.5z"
                                        clip-rule="evenodd" />
                                </svg>
                            </button>

                            <div class="space-y-3">
                                <!-- Row 1: Product -->
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 mb-1">Product</label>
                                    <ProductAutocomplete :items="products" :model-value="item.product_id ?? null"
                                        :initial-display="item.name || (item.product_id && products.find(p => p.id === item.product_id)?.name) || ''"
                                        @update:model-value="(val: any) => item.product_id = val"
                                        @select="(prod: any) => onProductSelect(item, prod)"
                                        @change="(val: string) => { item.name = val; item.product_id = null; }" />
                                </div>

                                <!-- Row 2: Notes (Optional) -->
                                <div v-if="form.meta.display_options.show_description">
                                    <input type="text" v-model="item.description" placeholder="Notes"
                                        class="block w-full rounded-md border-0 py-1.5 px-2 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 text-sm" />
                                </div>

                                <!-- Row 3: HSN, Qty, Price -->
                                <div class="grid grid-cols-3 gap-2">
                                    <div v-if="form.meta.display_options.show_hsn">
                                        <label class="block text-xs font-medium text-gray-700 mb-1">HSN</label>
                                        <input type="text" v-model="item.hsn_code"
                                            class="block w-full rounded-md border-0 py-1.5 px-2 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 text-sm" />
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-gray-700 mb-1">Qty</label>
                                        <input type="number" v-model.number="item.quantity" min="0.01" step="0.01"
                                            class="block w-full rounded-md border-0 py-1.5 px-2 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 text-sm" />
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-gray-700 mb-1">Price</label>
                                        <input type="number" v-model.number="item.unit_price" min="0" step="0.01"
                                            class="block w-full rounded-md border-0 py-1.5 px-2 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 text-sm" />
                                    </div>
                                </div>

                                <!-- Row 4: Discount, Tax, Total -->
                                <div class="grid grid-cols-3 gap-2 items-center">
                                    <div v-if="form.meta.display_options.show_discount">
                                        <label class="block text-xs font-medium text-gray-700 mb-1">Disc</label>
                                        <input type="number" v-model.number="item.discount" min="0" step="0.01"
                                            class="block w-full rounded-md border-0 py-1.5 px-2 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 text-sm" />
                                    </div>
                                    <div v-if="form.meta.display_options.show_gst_breakdown">
                                        <label class="block text-xs font-medium text-gray-700 mb-1">Tax %</label>
                                        <input type="number" v-model.number="item.tax_rate" min="0" step="0.1"
                                            class="block w-full rounded-md border-0 py-1.5 px-2 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 text-sm" />
                                    </div>
                                    <div class="col-span-1 text-right">
                                        <label class="block text-xs font-medium text-gray-700 mb-1">Total</label>
                                        <span class="text-sm font-bold text-gray-900">{{
                                            formatCurrency(calculateLineTotal(item)) }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <button @click="addItem" type="button"
                            class="w-full py-2 flex items-center justify-center rounded-md border-2 border-dashed border-indigo-300 text-sm font-medium text-indigo-600 hover:border-indigo-400 hover:text-indigo-500">
                            + Add Item
                        </button>
                    </div>

                    <!-- Desktop View: Table -->
                    <div class="hidden sm:block">
                        <table class="min-w-full divide-y divide-gray-200" style="min-width: 800px;">
                            <thead>
                                <tr>
                                    <th
                                        class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Product</th>
                                    <th v-if="form.meta.display_options.show_description"
                                        class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Notes</th>
                                    <th v-if="form.meta.display_options.show_hsn"
                                        class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        HSN/SAC
                                    </th>
                                    <th
                                        class="px-2 py-2 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Qty
                                    </th>
                                    <th
                                        class="px-2 py-2 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Price
                                    </th>
                                    <th v-if="form.meta.display_options.show_discount"
                                        class="px-2 py-2 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Disc %
                                    </th>
                                    <th v-if="form.meta.display_options.show_discount"
                                        class="px-2 py-2 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Sell Price
                                    </th>
                                    <th v-if="form.meta.display_options.show_gst_breakdown"
                                        class="px-2 py-2 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Tax %
                                    </th>
                                    <th
                                        class="px-2 py-2 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Total
                                    </th>
                                    <th
                                        class="px-2 py-2 text-right text-xs font-medium text-gray-500 uppercase tracking-wider w-10">
                                    </th>
                                </tr>
                            </thead>
                            <tbody id="invoice-items-body" class="divide-y divide-gray-200">
                                <tr v-for="(item, index) in form.items" :key="index">
                                    <td class="px-1 py-1">
                                        <div class="relative w-full min-w-[12rem]">
                                            <ProductAutocomplete :items="products"
                                                :model-value="item.product_id ?? null"
                                                :initial-display="item.name || (item.product_id && products.find(p => p.id === item.product_id)?.name) || ''"
                                                @update:model-value="(val: any) => item.product_id = val"
                                                @select="(prod: any) => onProductSelect(item, prod)"
                                                @change="(val: string) => { item.name = val; item.product_id = null; }" />
                                        </div>
                                    </td>
                                    <td v-if="form.meta.display_options.show_description" class="px-1 py-1">
                                        <input type="text" v-model="item.description" placeholder="Notes"
                                            class="block w-full rounded-md border-0 py-1 px-2 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 text-xs leading-6" />
                                    </td>
                                    <td v-if="form.meta.display_options.show_hsn" class="px-1 py-1">
                                        <input type="text" v-model="item.hsn_code" placeholder="HSN"
                                            class="block w-full rounded-md border-0 py-1 px-2 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 text-xs leading-6" />
                                    </td>
                                    <td class="px-1 py-1">
                                        <input type="number" v-model.number="item.quantity" min="0.01" step="any"
                                            @input="calculateDiscountAmount(item)"
                                            class="block w-full rounded-md border-0 py-1 px-2 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 text-xs leading-6 text-right"
                                            required />
                                    </td>
                                    <td class="px-1 py-1">
                                        <input type="number" v-model.number="item.unit_price" min="0" step="any"
                                            @input="calculateDiscountAmount(item)"
                                            class="block w-full rounded-md border-0 py-1 px-2 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 text-xs leading-6 text-right"
                                            required />
                                    </td>
                                    <td v-if="form.meta.display_options.show_discount" class="px-1 py-1">
                                        <!-- Discount Percent Input -->
                                        <input type="number" v-model.number="item.discount_percent" min="0" max="100"
                                            step="any" @input="calculateDiscountAmount(item)"
                                            class="block w-full rounded-md border-0 py-1 px-2 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 text-xs leading-6 text-right" />
                                    </td>
                                    <td v-if="form.meta.display_options.show_discount" class="px-1 py-1">
                                        <!-- Calculated Sell Price Display -->
                                        <input type="text"
                                            :value="formatCurrency((item.unit_price * (1 - (item.discount_percent || 0) / 100)))"
                                            disabled
                                            class="block w-full rounded-md border-0 py-1 px-2 text-gray-500 bg-gray-50 shadow-sm ring-1 ring-inset ring-gray-300 text-xs leading-6 text-right" />
                                    </td>
                                    <td v-if="form.meta.display_options.show_gst_breakdown" class="px-1 py-1">
                                        <input type="number" v-model.number="item.tax_rate" min="0" step="0.1"
                                            class="block w-full rounded-md border-0 py-1 px-2 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 text-xs leading-6 text-right" />
                                    </td>
                                    <td
                                        class="px-2 py-1 text-right text-sm text-gray-900 font-medium whitespace-nowrap">
                                        {{ formatCurrency(calculateLineTotal(item)) }}
                                    </td>
                                    <td class="px-2 py-1 text-right">
                                        <button @click="removeItem(index)" type="button"
                                            class="text-red-600 hover:text-red-900">
                                            <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd"
                                                    d="M8.75 1A2.75 2.75 0 006 3.75v.443c-.795.077-1.584.176-2.365.298a.75.75 0 10.23 1.482l.149-.022.841 10.518A2.75 2.75 0 007.596 19h4.807a2.75 2.75 0 002.742-2.53l.841-10.52.149.023a.75.75 0 00.23-1.482A41.03 41.03 0 0014 4.193V3.75A2.75 2.75 0 0011.25 1h-2.5zM10 4c.84 0 1.673.025 2.5.075V3.75c0-.69-.56-1.25-1.25-1.25h-2.5c-.69 0-1.25.56-1.25 1.25v.325C8.327 4.025 9.16 4 10 4zM8.58 7.72a.75.75 0 00-1.5.06l.3 7.5a.75.75 0 101.5-.06l-.3-7.5zm4.34.06a.75.75 0 10-1.5-.06l-.3 7.5a.75.75 0 101.5.06l.3-7.5z"
                                                    clip-rule="evenodd" />
                                            </svg>
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        <button @click="addItem" type="button"
                            class="mt-4 text-sm font-semibold text-indigo-600 hover:text-indigo-500">
                            + Add Item
                        </button>
                    </div>

                    <!-- Totals -->
                    <div class="mt-8 flex justify-end">

                        <div class="w-full max-w-xs space-y-2">
                            <div class="flex justify-between text-sm text-gray-600">
                                <span>Subtotal</span>
                                <span>{{ formatCurrency(totals.subtotal) }}</span>
                            </div>

                            <!-- Tax Breakdown -->
                            <div v-if="totals.taxType === 'IGST'" class="flex justify-between text-sm text-gray-600">
                                <span>IGST</span>
                                <span>{{ formatCurrency(totals.igst) }}</span>
                            </div>
                            <template v-else>
                                <div class="flex justify-between text-sm text-gray-600">
                                    <span>CGST</span>
                                    <span>{{ formatCurrency(totals.cgst) }}</span>
                                </div>
                                <div class="flex justify-between text-sm text-gray-600">
                                    <span>SGST</span>
                                    <span>{{ formatCurrency(totals.sgst) }}</span>
                                </div>
                            </template>

                            <div class="flex justify-between text-base font-semibold text-gray-900 border-t pt-2">
                                <span>Grand Total</span>
                                <span>{{ formatCurrency(totals.grandTotal) }}</span>
                            </div>

                            <div class="text-xs text-right text-gray-500 mt-1">
                                Place of Supply: {{ totals.posState }} ({{ totals.taxType }})
                            </div>
                        </div>
                    </div>
                </div>
            </div>


            <!-- Notes & Terms -->
            <div class="bg-white shadow-sm ring-1 ring-gray-900/5 sm:rounded-xl">
                <div class="px-4 py-5 sm:p-6">
                    <div class="grid grid-cols-1 gap-x-6 gap-y-4 sm:grid-cols-2 sm:gap-y-8">
                        <div>
                            <label for="notes" class="block text-sm font-medium leading-6 text-gray-900">Notes</label>
                            <div class="mt-2">
                                <textarea id="notes" v-model="form.notes" rows="3"
                                    class="block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6"></textarea>
                            </div>
                        </div>
                        <div>
                            <label for="terms" class="block text-sm font-medium leading-6 text-gray-900">Terms &
                                Conditions</label>
                            <div class="mt-2">
                                <textarea id="terms" v-model="form.terms" rows="3"
                                    class="block w-full rounded-md border-0 py-2 px-3.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6"></textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bottom Actions -->
            <div
                class="mt-8 flex items-center justify-between gap-x-6 border-t border-gray-200 bg-gray-50 px-4 py-4 sm:px-8">
                <button @click="$router.back()" type="button"
                    class="text-sm font-semibold text-gray-900 hover:text-gray-700">
                    Cancel
                </button>
                <div class="flex gap-2">
                    <button @click="save('draft')" :disabled="loading" type="button"
                        class="inline-flex items-center rounded-lg bg-white px-4 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50">
                        {{ loading ? 'Saving...' : 'Save Draft' }}
                    </button>
                    <button @click="save('sent')" :disabled="loading" type="button"
                        class="inline-flex items-center rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700">
                        {{ loading ? 'Saving...' : 'Save & Finalize' }}
                    </button>
                </div>
            </div>

        </form>
    </AppLayout>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted, watch, nextTick } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import AppLayout from '../../layouts/AppLayout.vue'
import { useInvoiceStore, type InvoiceItem } from '../../stores/invoice'
import { usePartyStore } from '../../stores/party'
import { useProductStore, type Product } from '../../stores/product'
import { useAuthStore } from '../../stores/auth'
import { storeToRefs } from 'pinia'
import StateSelect from '../../components/StateSelect.vue'
import ProductAutocomplete from '../../components/ProductAutocomplete.vue'
import CustomSelect from '../../components/CustomSelect.vue'

const documentTypeOptions = [
    {
        label: 'Sales Documents',
        options: [
            { label: 'Tax Invoice (GST)', value: 'tax_invoice', description: 'Regular GST invoice for sales' },
            { label: 'Bill of Supply (No GST)', value: 'bill_of_supply', description: 'For composition/exempted goods' },
            { label: 'Proforma Invoice (Estimate)', value: 'proforma_invoice', description: 'Quotation - not a tax invoice' }
        ]
    },
    {
        label: 'Adjustments',
        options: [
            { label: 'Credit Note (Return/Reduction)', value: 'credit_note', description: 'Issue when reducing invoice amount (returns, discounts)' },
            { label: 'Debit Note (Additional Charges)', value: 'debit_note', description: 'Issue when increasing invoice amount' }
        ]
    },
    {
        label: 'Logistics',
        options: [
            { label: 'Delivery Challan', value: 'delivery_challan', description: 'For transport of goods without invoice' }
        ]
    }
]

import client from '../../api/client'

const router = useRouter()
const route = useRoute()
const invoiceStore = useInvoiceStore()
const partyStore = usePartyStore()
const productStore = useProductStore()
const authStore = useAuthStore()

const { loading } = storeToRefs(invoiceStore)

const isEditMode = computed(() => route.params.id !== undefined)

// Detect default type from route name
const defaultType = computed(() => {
    const routeName = route.name?.toString() || ''
    if (routeName.includes('quotation')) return 'proforma_invoice'
    if (routeName.includes('credit-note')) return 'credit_note'
    if (routeName.includes('debit-note')) return 'debit_note'
    if (routeName.includes('delivery-challan')) return 'delivery_challan'
    return 'tax_invoice' // Default for invoice routes
})

// Get document type label for display
const getDocumentTypeLabel = () => {
    switch (form.value.type) {
        case 'bill_of_supply': return 'Bill of Supply'
        case 'proforma_invoice': return 'Quotation'
        case 'delivery_challan': return 'Delivery Challan'
        case 'credit_note': return 'Credit Note'
        case 'debit_note': return 'Debit Note'
        default: return 'Invoice'
    }
}

const customers = ref<any[]>([])
const products = ref<Product[]>([])

// Display Options State
const showDisplayOptions = ref(true) // Changed to true - visible by default
const rememberSettings = ref(false)

const form = ref({
    invoice_number: '',
    type: 'tax_invoice', // Default to tax invoice
    party_id: '',
    date: new Date().toISOString().split('T')[0],
    due_date: new Date().toISOString().split('T')[0],
    items: [] as (InvoiceItem & { discount_percent?: number })[],
    notes: '',
    terms: '',
    challan_no: '',
    vehicle_no: '',
    eway_bill_no: '',
    po_number: '',
    status: 'draft' as 'draft' | 'sent' | 'paid',
    meta: {
        display_options: {
            show_transport_details: false,
            show_hsn: true,
            show_gst_breakdown: true,
            show_discount: false,
            show_qr_bank_details: false,
            show_shipping_address: false,
            show_description: true
        },
        billing_address: {
            street: '',
            city: '',
            state: '',
            zip: ''
        },
        shipping_address: {
            street: '',
            city: '',
            state: '',
            zip: ''
        },
        copy_type: 'original' as 'original' | 'duplicate' | 'triplicate'
    }
})

// Helper Methods for Display Options
const getActiveOptionsCount = () => {
    const opts = form.value.meta.display_options
    return Object.values(opts).filter(v => v === true).length
}

const applyPreset = (preset: string) => {
    switch (preset) {
        case 'simple':
            form.value.meta.display_options = {
                show_transport_details: false,
                show_hsn: false,
                show_gst_breakdown: false,
                show_discount: false,
                show_qr_bank_details: false,
                show_shipping_address: false,
                show_description: true
            }
            break
        case 'gst':
            form.value.meta.display_options = {
                show_transport_details: false,
                show_hsn: true,
                show_gst_breakdown: true,
                show_discount: false,
                show_qr_bank_details: true,
                show_shipping_address: false,
                show_description: true
            }
            break
        case 'full':
            form.value.meta.display_options = {
                show_transport_details: true,
                show_hsn: true,
                show_gst_breakdown: true,
                show_discount: true,
                show_qr_bank_details: true,
                show_shipping_address: true,
                show_description: true
            }
            break
    }
}

// Auto-fill address when customer changes
watch(() => form.value.party_id, (newId) => {
    if (newId) {
        const party = customers.value.find(c => c.id === newId)
        if (party) {
            if (!form.value.meta.billing_address.street || !isEditMode.value) {
                form.value.meta.billing_address = party.billing_address ? { ...party.billing_address } : { street: '', city: '', state: '', zip: '' }
                form.value.meta.shipping_address = party.shipping_address ? { ...party.shipping_address } : { street: '', city: '', state: '', zip: '' }
            }
        }
    }
})

const addItem = async () => {
    form.value.items.push({
        name: '',
        description: '',
        hsn_code: '',
        quantity: 1,
        unit_price: 0,
        discount: 0,
        discount_percent: 0,
        tax_rate: 0,
        tax_amount: 0,
        total: 0
    })

    // Auto-focus the new product input
    await nextTick()
    const lastInput = document.querySelector('#invoice-items-body tr:last-child input') as HTMLInputElement
    if (lastInput) lastInput.focus()
}

const removeItem = (index: number) => {
    form.value.items.splice(index, 1)
}

const calculateLineTotal = (item: InvoiceItem) => {
    const qty = Number(item.quantity) || 0
    const price = Number(item.unit_price) || 0
    // Only apply discount if the option is enabled
    const discount = form.value.meta.display_options.show_discount ? (Number(item.discount) || 0) : 0
    const taxRate = Number(item.tax_rate) || 0

    const sub = (qty * price) - discount
    const taxable = sub > 0 ? sub : 0
    const tax = taxable * (taxRate / 100)
    return taxable + tax
}

const calculateDiscountAmount = (item: InvoiceItem & { discount_percent?: number }) => {
    // If discount percent is provided, calculate discount amount
    // Discount Amount = (Quantity * Unit Price * Percent) / 100
    // This is Total Discount for the line
    const qty = Number(item.quantity) || 0
    const price = Number(item.unit_price) || 0
    const percent = Number(item.discount_percent) || 0

    if (percent > 0) {
        item.discount = (qty * price * percent) / 100
    } else {
        // If percent is 0, we might want to keep manual discount? 
        // For now, let's assume if percent is touched, it drives discount.
        // But what if user edits discount amount directly? 
        // We should probably allow both. But for this specific task "MRP - Disc% -> Sell Price", 
        // percent is the driver.
        item.discount = 0
    }
}

const totals = computed(() => {
    let subtotal = 0
    let cgst = 0
    let sgst = 0
    let igst = 0

    const businessState = authStore.activeBusiness?.meta?.state?.toLowerCase()
    const selectedCustomer = customers.value.find(c => c.id === form.value.party_id)
    const customerState = (selectedCustomer?.shipping_address?.state || selectedCustomer?.billing_address?.state || '').toLowerCase()

    const isInterState = businessState && customerState && businessState !== customerState
    const taxType = isInterState ? 'IGST' : 'CGST+SGST'
    const posState = customerState || 'Unknown'

    form.value.items.forEach(item => {
        const qty = Number(item.quantity) || 0
        const price = Number(item.unit_price) || 0
        // Only apply discount if the option is enabled
        const discount = form.value.meta.display_options.show_discount ? (Number(item.discount) || 0) : 0
        const taxRate = Number(item.tax_rate) || 0

        const lineSub = (qty * price) - discount
        const taxable = lineSub > 0 ? lineSub : 0
        const lineTax = taxable * (taxRate / 100)

        subtotal += taxable

        if (isInterState) {
            igst += lineTax
        } else {
            cgst += lineTax / 2
            sgst += lineTax / 2
        }
    })

    return {
        subtotal,
        cgst,
        sgst,
        igst,
        taxType,
        posState,
        grandTotal: subtotal + cgst + sgst + igst
    }
})

const formatCurrency = (value: number) => {
    return new Intl.NumberFormat('en-IN', { style: 'currency', currency: 'INR' }).format(value)
}

const loadCustomers = async () => {
    try {
        await partyStore.fetchParties({ type: 'customer', per_page: 100 })
        customers.value = partyStore.parties
    } catch (e) {
        console.error(e)
    }
}

const loadProducts = async () => {
    try {
        await productStore.fetchProducts({ per_page: 100 })
        products.value = productStore.products
    } catch (e) {
        console.error('Failed to load products', e)
    }
}

const onProductSelect = (item: InvoiceItem, product: any) => {
    if (product) {
        item.name = product.name
        item.hsn_code = product.hsn_code || ''

        const rate = Number(product.tax_rate) || 0
        const rawPrice = Number(product.sale_price) || 0

        if (product.is_tax_inclusive && rate > 0) {
            // Reverse calculate exclusive base price
            item.unit_price = Number((rawPrice / (1 + (rate / 100))).toFixed(4))
        } else {
            item.unit_price = rawPrice
        }

        item.tax_rate = rate
        if (!item.quantity) item.quantity = 1

        // Ensure discount and totals are recalculated
        calculateDiscountAmount(item)
    }
}

const loadInvoice = async () => {
    // Check for Quick Note transfer
    if (route.query.from_quick_note) {
        const pending = localStorage.getItem('pending_invoice_items')
        if (pending) {
            try {
                const data = JSON.parse(pending)
                if (data.items && Array.isArray(data.items)) {
                    form.value.items = data.items
                }
                // Cleanup
                localStorage.removeItem('pending_invoice_items')
            } catch (e) {
                console.error("Failed to parse pending items", e)
            }
        }

        // Also load default business settings
        const business = authStore.activeBusiness
        if (business && business.meta) {
            form.value.notes = business.meta.default_notes || ''
            form.value.terms = business.meta.default_terms || ''
        }
        return
    }

    if (!isEditMode.value) {
        addItem()
        const business = authStore.activeBusiness
        if (business && business.meta) {
            form.value.notes = business.meta.default_notes || ''
            form.value.terms = business.meta.default_terms || ''

            // Load saved display preferences or apply smart defaults
            if (business.meta.invoice_display_preferences) {
                form.value.meta.display_options = {
                    show_transport_details: business.meta.invoice_display_preferences.show_transport_details ?? false,
                    show_hsn: business.meta.invoice_display_preferences.show_hsn_sac ?? true,
                    show_gst_breakdown: business.meta.invoice_display_preferences.show_gst_breakdown ?? true,
                    show_discount: business.meta.invoice_display_preferences.show_discount_column ?? false,
                    show_qr_bank_details: business.meta.invoice_display_preferences.show_bank_qr ?? false,
                    show_shipping_address: business.meta.invoice_display_preferences.show_shipping_address ?? false,
                    show_description: business.meta.invoice_display_preferences.show_notes ?? true
                }
                rememberSettings.value = true
            } else {
                // Smart defaults based on business profile
                const hasGST = !!business.gstin
                const hasBankDetails = !!business.bank_name

                form.value.meta.display_options = {
                    show_transport_details: false,
                    show_hsn: hasGST,
                    show_gst_breakdown: hasGST,
                    show_discount: false,
                    show_qr_bank_details: hasBankDetails,
                    show_shipping_address: false,
                    show_description: true
                }
            }
        }
        return
    }

    try {
        const invoice = await invoiceStore.fetchInvoice(route.params.id as string)
        if (invoice) {
            // Correct date format for input=date (YYYY-MM-DD)
            const formatDate = (d: string) => d ? new Date(d).toISOString().split('T')[0] : ''

            form.value = {
                invoice_number: invoice.invoice_number,
                type: invoice.type || 'tax_invoice',
                party_id: invoice.party_id,
                date: formatDate(invoice.date),
                due_date: formatDate(invoice.due_date),
                items: invoice.items.map((i: any) => ({
                    ...i,
                    name: i.name || (!i.product_id ? i.description : ''),
                    // Back-calculate percent if missing but discount exists
                    discount_percent: i.discount && i.unit_price && i.quantity ? ((i.discount / (i.quantity * i.unit_price)) * 100) : 0
                })),
                notes: invoice.notes || '',
                terms: invoice.terms || '',
                challan_no: invoice.challan_no || '',
                vehicle_no: invoice.vehicle_no || '',
                eway_bill_no: invoice.eway_bill_no || '',
                po_number: invoice.po_number || '',
                status: invoice.status || 'draft',
                meta: {
                    display_options: {
                        show_transport_details: invoice.meta?.display_options?.show_transport_details ??
                            (!!invoice.challan_no || !!invoice.vehicle_no || !!invoice.eway_bill_no || !!invoice.po_number),
                        show_hsn: invoice.meta?.display_options?.show_hsn ?? true,
                        show_gst_breakdown: invoice.meta?.display_options?.show_gst_breakdown ?? true,
                        show_discount: invoice.meta?.display_options?.show_discount ?? false,
                        show_qr_bank_details: invoice.meta?.display_options?.show_qr_bank_details ?? false,
                        show_shipping_address: invoice.meta?.display_options?.show_shipping_address ?? false,
                        show_description: invoice.meta?.display_options?.show_description ?? true
                    },
                    billing_address: invoice.meta?.billing_address || { street: '', city: '', state: '', zip: '' },
                    shipping_address: invoice.meta?.shipping_address || { street: '', city: '', state: '', zip: '' },
                    copy_type: invoice.meta?.copy_type || 'original'
                }
            }

            // Populate addresses if missing from invoice meta but present in party
            if (!form.value.meta.billing_address?.street && invoice.party?.billing_address) {
                form.value.meta.billing_address = { ...invoice.party.billing_address }
            }
            if (!form.value.meta.shipping_address?.street && invoice.party?.shipping_address) {
                form.value.meta.shipping_address = { ...invoice.party.shipping_address }
            }
        }
    } catch (e: any) {
        alert(e.response?.data?.message || 'Failed to load invoice')
        router.push('/invoices')
    }
}

const save = async (status: 'draft' | 'sent') => {
    if (!form.value.party_id) {
        alert('Please select a customer')
        return
    }
    if (form.value.items.length === 0) {
        alert('Please add at least one item')
        return
    }

    const payload = {
        ...form.value,
        status,
        subtotal: totals.value.subtotal,
        tax_total: totals.value.cgst + totals.value.sgst + totals.value.igst,
        grand_total: totals.value.grandTotal,
        business_id: authStore.currentBusinessId
    }

    try {
        if (isEditMode.value) {
            await invoiceStore.updateInvoice(route.params.id as string, payload as any)
            alert('Invoice updated successfully')
        } else {
            await invoiceStore.createInvoice(payload as any)
            alert('Invoice created successfully')
        }
        router.push('/invoices')
    } catch (e) {
        console.error(e)
        alert('Failed to save invoice')
    }
}

// Save preferences when Remember Settings is checked and options change
const savePreferences = async () => {
    const business = authStore.activeBusiness
    if (!business || !rememberSettings.value) return

    try {
        await client.post(`/businesses/${business.id}/invoice-preferences`, {
            show_hsn_sac: form.value.meta.display_options.show_hsn,
            show_gst_breakdown: form.value.meta.display_options.show_gst_breakdown,
            show_bank_qr: form.value.meta.display_options.show_qr_bank_details,
            show_notes: form.value.meta.display_options.show_description,
            show_shipping_address: form.value.meta.display_options.show_shipping_address,
            show_discount_column: form.value.meta.display_options.show_discount,
            show_transport_details: form.value.meta.display_options.show_transport_details
        })
    } catch (e) {
        console.error('Failed to save invoice preferences:', e)
    }
}

// Watch for changes in display options when Remember is checked
watch(() => form.value.meta.display_options, () => {
    if (rememberSettings.value) {
        savePreferences()
    }
}, { deep: true })

onMounted(() => {
    loadCustomers()
    loadProducts()
    loadInvoice()

    // Auto-set document type from route for new documents
    if (!isEditMode.value) {
        form.value.type = defaultType.value
    }

    window.addEventListener('keydown', handleKeydown)
})

onUnmounted(() => {
    window.removeEventListener('keydown', handleKeydown)
})

function handleKeydown(e: KeyboardEvent) {
    if ((e.ctrlKey || e.metaKey) && e.key === 's') {
        e.preventDefault()
        save('sent') // Default to Save Invoice
    }
}
</script>
