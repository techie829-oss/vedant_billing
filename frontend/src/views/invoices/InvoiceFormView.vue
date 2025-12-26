<template>
    <AppLayout>

        <div class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-2xl font-bold text-gray-900">
                    {{ isEditMode ? 'Edit Invoice' : 'Create New Invoice' }}
                </h2>
            </div>
            <div class="mt-4 flex sm:mt-0 sm:ml-4">
                <button @click="$router.back()" type="button"
                    class="inline-flex items-center rounded-lg bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50">Cancel</button>
                <button @click="save('draft')" :disabled="loading" type="button"
                    class="ml-3 inline-flex items-center rounded-lg bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50">
                    Save as Draft
                </button>
                <button @click="save('sent')" :disabled="loading" type="button"
                    class="ml-3 inline-flex items-center rounded-lg bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
                    {{ loading ? 'Saving...' : 'Save & Send' }}
                </button>
            </div>
        </div>

        <form @submit.prevent="save('draft')" class="space-y-6">
            <!-- Display Options -->
            <div class="bg-white shadow-sm ring-1 ring-gray-900/5 sm:rounded-xl">
                <div class="px-4 py-6 sm:p-8">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-base font-semibold leading-7 text-gray-900">Display Options</h3>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4 mb-6">
                        <div class="relative flex items-start">
                            <div class="flex h-6 items-center">
                                <input id="show_eway" type="checkbox"
                                    v-model="form.meta.display_options.show_eway_details"
                                    class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-600">
                            </div>
                            <div class="ml-3 text-sm leading-6">
                                <label for="show_eway" class="font-medium text-gray-900">Show Transport/Order
                                    Details</label>
                            </div>
                        </div>
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
                                <input id="show_bank" type="checkbox"
                                    v-model="form.meta.display_options.show_qr_bank_details"
                                    class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-600">
                            </div>
                            <div class="ml-3 text-sm leading-6">
                                <label for="show_bank" class="font-medium text-gray-900">Show Bank Details &
                                    QR</label>
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

                    <!-- Transport Inputs (Conditionally Shown) -->
                    <div v-if="form.meta.display_options.show_eway_details"
                        class="grid grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-4 border-t border-gray-200 pt-6">
                        <div class="sm:col-span-1">
                            <label for="challan_no" class="block text-sm font-medium leading-6 text-gray-900">Challan
                                No.</label>
                            <div class="mt-2">
                                <input type="text" id="challan_no" v-model="form.challan_no"
                                    class="block w-full rounded-md border-0 py-2 px-3.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6" />
                            </div>
                        </div>
                        <div class="sm:col-span-1">
                            <label for="eway_bill_no" class="block text-sm font-medium leading-6 text-gray-900">E-Way
                                Bill No.</label>
                            <div class="mt-2">
                                <input type="text" id="eway_bill_no" v-model="form.eway_bill_no"
                                    class="block w-full rounded-md border-0 py-2 px-3.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6" />
                            </div>
                        </div>
                        <div class="sm:col-span-1">
                            <label for="vehicle_no" class="block text-sm font-medium leading-6 text-gray-900">Vehicle
                                No.</label>
                            <div class="mt-2">
                                <input type="text" id="vehicle_no" v-model="form.vehicle_no"
                                    class="block w-full rounded-md border-0 py-2 px-3.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6" />
                            </div>
                        </div>
                        <div class="sm:col-span-1">
                            <label for="po_number" class="block text-sm font-medium leading-6 text-gray-900">PO
                                Number</label>
                            <div class="mt-2">
                                <input type="text" id="po_number" v-model="form.po_number"
                                    class="block w-full rounded-md border-0 py-2 px-3.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6" />
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Customer & Dates -->
            <div class="bg-white shadow-sm ring-1 ring-gray-900/5 sm:rounded-xl">
                <div class="px-4 py-6 sm:p-8">
                    <div class="grid max-w-2xl grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6">
                        <div class="sm:col-span-3">
                            <label for="customer"
                                class="block text-sm font-medium leading-6 text-gray-900">Customer</label>
                            <div class="mt-2">
                                <div class="relative">
                                    <select id="customer" v-model="form.party_id" required
                                        class="block w-full appearance-none rounded-md border-0 py-2 pl-3.5 pr-10 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
                                        <option value="" disabled>Select Customer</option>
                                        <option v-for="party in customers" :key="party.id" :value="party.id">{{
                                            party.name }}</option>
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
                        </div>

                        <div class="sm:col-span-3">
                            <!-- Invoice Number is usually auto-generated by backend, but can be shown if editing -->
                            <label class="block text-sm font-medium leading-6 text-gray-900">Invoice Number</label>
                            <div class="mt-2">
                                <input type="text" disabled
                                    :value="isEditMode ? form.invoice_number : '(Auto-generated)'"
                                    class="block w-full rounded-md border-0 py-2 px-3.5 text-gray-500 bg-gray-50 shadow-sm ring-1 ring-inset ring-gray-300 sm:text-sm sm:leading-6" />
                            </div>
                        </div>

                        <div class="sm:col-span-3">
                            <label for="date" class="block text-sm font-medium leading-6 text-gray-900">Invoice
                                Date</label>
                            <div class="mt-2">
                                <input type="date" id="date" v-model="form.date" required
                                    class="block w-full rounded-md border-0 py-2 px-3.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6" />
                            </div>
                        </div>

                        <div class="sm:col-span-3">
                            <label for="due_date" class="block text-sm font-medium leading-6 text-gray-900">Due
                                Date</label>
                            <div class="mt-2">
                                <input type="date" id="due_date" v-model="form.due_date" required
                                    class="block w-full rounded-md border-0 py-2 px-3.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6" />
                            </div>
                        </div>
                    </div>
                </div>
            </div>



            <!-- Billing Addresses -->
            <div v-if="form.party_id" class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Billing Address -->
                <div class="bg-white shadow-sm ring-1 ring-gray-900/5 sm:rounded-xl">
                    <div class="px-4 py-6 sm:p-8">
                        <h3 class="text-base font-semibold leading-7 text-gray-900 mb-4">Billing Address</h3>
                        <div class="grid grid-cols-1 gap-x-6 gap-y-4 sm:grid-cols-6">
                            <div class="sm:col-span-6">
                                <label class="block text-sm font-medium leading-6 text-gray-900">Street</label>
                                <input type="text" v-model="form.meta.billing_address.street"
                                    class="mt-2 block w-full rounded-md border-0 py-2 px-3.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6" />
                            </div>
                            <div class="sm:col-span-2">
                                <label class="block text-sm font-medium leading-6 text-gray-900">City</label>
                                <input type="text" v-model="form.meta.billing_address.city"
                                    class="mt-2 block w-full rounded-md border-0 py-2 px-3.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6" />
                            </div>
                            <div class="sm:col-span-2">
                                <label class="block text-sm font-medium leading-6 text-gray-900">State</label>
                                <div class="relative mt-2">
                                    <select v-model="form.meta.billing_address.state"
                                        class="block w-full appearance-none rounded-md border-0 py-2 pl-3.5 pr-10 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
                                        <option value="" disabled>Select State</option>
                                        <option v-for="state in states" :key="state.code" :value="state.name">
                                            {{ state.name }} ({{ state.code }})
                                        </option>
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
                            <div class="sm:col-span-2">
                                <label class="block text-sm font-medium leading-6 text-gray-900">ZIP</label>
                                <input type="text" v-model="form.meta.billing_address.zip"
                                    class="mt-2 block w-full rounded-md border-0 py-2 px-3.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6" />
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Shipping Address (Conditional) -->
                <div v-if="form.meta.display_options.show_shipping_address"
                    class="bg-white shadow-sm ring-1 ring-gray-900/5 sm:rounded-xl">
                    <div class="px-4 py-6 sm:p-8">
                        <h3 class="text-base font-semibold leading-7 text-gray-900 mb-4">Shipping Address</h3>
                        <div class="grid grid-cols-1 gap-x-6 gap-y-4 sm:grid-cols-6">
                            <div class="sm:col-span-6">
                                <label class="block text-sm font-medium leading-6 text-gray-900">Street</label>
                                <input type="text" v-model="form.meta.shipping_address.street"
                                    class="mt-2 block w-full rounded-md border-0 py-2 px-3.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6" />
                            </div>
                            <div class="sm:col-span-2">
                                <label class="block text-sm font-medium leading-6 text-gray-900">City</label>
                                <input type="text" v-model="form.meta.shipping_address.city"
                                    class="mt-2 block w-full rounded-md border-0 py-2 px-3.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6" />
                            </div>
                            <div class="sm:col-span-2">
                                <label class="block text-sm font-medium leading-6 text-gray-900">State</label>
                                <div class="relative mt-2">
                                    <select v-model="form.meta.shipping_address.state"
                                        class="block w-full appearance-none rounded-md border-0 py-2 pl-3.5 pr-10 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
                                        <option value="" disabled>Select State</option>
                                        <option v-for="state in states" :key="state.code" :value="state.name">
                                            {{ state.name }} ({{ state.code }})
                                        </option>
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
                            <div class="sm:col-span-2">
                                <label class="block text-sm font-medium leading-6 text-gray-900">ZIP</label>
                                <input type="text" v-model="form.meta.shipping_address.zip"
                                    class="mt-2 block w-full rounded-md border-0 py-2 px-3.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6" />
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Line Items -->
            <div class="bg-white shadow-sm ring-1 ring-gray-900/5 sm:rounded-xl">
                <div class="px-4 py-6 sm:p-8">
                    <h3 class="text-base font-semibold leading-7 text-gray-900 mb-4">Items</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead>
                                <tr>
                                    <th
                                        class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-40">
                                        Product</th>
                                    <th v-if="form.meta.display_options.show_description"
                                        class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-1/4">
                                        Notes</th>
                                    <th
                                        class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-24">
                                        HSN/SAC</th>
                                    <th
                                        class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-20">
                                        Qty</th>
                                    <th
                                        class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-28">
                                        Price</th>
                                    <th v-if="form.meta.display_options.show_discount"
                                        class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-24">
                                        Discount</th>
                                    <th
                                        class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-20">
                                        Tax %</th>
                                    <th
                                        class="px-2 py-2 text-right text-xs font-medium text-gray-500 uppercase tracking-wider w-28">
                                        Total</th>
                                    <th
                                        class="px-2 py-2 text-right text-xs font-medium text-gray-500 uppercase tracking-wider w-10">
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                <tr v-for="(item, index) in form.items" :key="index">
                                    <td class="px-2 py-1">
                                        <div class="relative w-full min-w-[12rem]">
                                            <ProductAutocomplete :items="products"
                                                :model-value="item.product_id ?? null"
                                                :initial-display="item.name || (item.product_id && products.find(p => p.id === item.product_id)?.name) || ''"
                                                @update:model-value="(val: any) => item.product_id = val"
                                                @select="(prod: any) => onProductSelect(item, prod)"
                                                @change="(val: string) => { item.name = val; item.product_id = null; }" />
                                        </div>
                                    </td>
                                    <td v-if="form.meta.display_options.show_description" class="px-2 py-1">
                                        <input type="text" v-model="item.description" placeholder="Notes (optional)"
                                            class="block w-full rounded-md border-0 py-1.5 px-2 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 text-xs sm:text-sm sm:leading-6" />
                                    </td>
                                    <td v-if="form.meta.display_options.show_hsn" class="px-2 py-1">
                                        <input type="text" v-model="item.hsn_code" placeholder="HSN"
                                            class="block w-full rounded-md border-0 py-1.5 px-2 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 text-xs sm:text-sm sm:leading-6" />
                                    </td>
                                    <td class="px-2 py-1">
                                        <input type="number" v-model.number="item.quantity" min="0.01" step="0.01"
                                            class="block w-full rounded-md border-0 py-1.5 px-2 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 text-xs sm:text-sm sm:leading-6"
                                            required />
                                    </td>
                                    <td class="px-2 py-1">
                                        <input type="number" v-model.number="item.unit_price" min="0" step="0.01"
                                            class="block w-full rounded-md border-0 py-1.5 px-2 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 text-xs sm:text-sm sm:leading-6"
                                            required />
                                    </td>
                                    <td v-if="form.meta.display_options.show_discount" class="px-2 py-1">
                                        <input type="number" v-model.number="item.discount" min="0" step="0.01"
                                            class="block w-full rounded-md border-0 py-1.5 px-2 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 text-xs sm:text-sm sm:leading-6" />
                                    </td>
                                    <td class="px-2 py-1">
                                        <input type="number" v-model.number="item.tax_rate" min="0" step="0.1"
                                            class="block w-full rounded-md border-0 py-1.5 px-2 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 text-xs sm:text-sm sm:leading-6" />
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
                <div class="px-4 py-6 sm:p-8">
                    <div class="grid grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-2">
                        <div>
                            <label for="notes" class="block text-sm font-medium leading-6 text-gray-900">Notes</label>
                            <div class="mt-2">
                                <textarea id="notes" v-model="form.notes" rows="3"
                                    class="block w-full rounded-md border-0 py-2 px-3.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6"></textarea>
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

        </form>
    </AppLayout>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import client from '../../api/client'
import AppLayout from '../../layouts/AppLayout.vue'
import { useInvoiceStore, type InvoiceItem } from '../../stores/invoice'
import { usePartyStore } from '../../stores/party'
import { useProductStore, type Product } from '../../stores/product'
import { useAuthStore } from '../../stores/auth'
import { storeToRefs } from 'pinia'
import ProductAutocomplete from '../../components/ProductAutocomplete.vue'

const router = useRouter()
const route = useRoute()
const invoiceStore = useInvoiceStore()
const partyStore = usePartyStore()
const productStore = useProductStore()
const authStore = useAuthStore()

const { loading } = storeToRefs(invoiceStore)

const isEditMode = computed(() => route.params.id !== undefined)

const customers = ref<any[]>([])
const products = ref<Product[]>([])

const form = ref({
    invoice_number: '',
    party_id: '',
    date: new Date().toISOString().split('T')[0],
    due_date: new Date(Date.now() + 15 * 24 * 60 * 60 * 1000).toISOString().split('T')[0],
    items: [] as InvoiceItem[],
    notes: '',
    terms: '',
    challan_no: '',
    eway_bill_no: '',
    vehicle_no: '',
    po_number: '',
    meta: {
        display_options: {
            show_eway_details: false,
            show_hsn: true,
            show_gst_breakdown: true,
            show_discount: false,
            show_qr_bank_details: true,
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
        }
    }
})



// Auto-fill address when customer changes
watch(() => form.value.party_id, (newId) => {
    // Only auto-fill if not in edit mode (or maybe we should? User might want to reset? Let's stick to only on create or change)
    // Actually, relying on `isEditMode` logic inside `watch` is complex.
    // Let's just say: IF the user changes the dropdown, we update.
    // But on initial load of Edit Mode, we should NOT overwrite if we already have meta.

    // We can check if the change is user-initiated.
    // Usually watchers fire on value change. 
    // On load, we set form value, watcher fires.

    if (newId) {
        const party = customers.value.find(c => c.id === newId)
        if (party) {
            // If addresses are empty, fill them. 
            // Or overwrite? User expects overwrite if they select a customer.
            // But on Edit Load, we set party_id, so this fires. We must be careful not to overwrite existing Invoice address.

            // Check if we already have populated address in meta (from loadInvoice)
            // If it's a fresh create (no ID), overwrite.
            // If it's edit, and party_id matches original, don't overwrite.
            // Simplified: only overwrite if we don't have existing meaningful data or user interaction?
            // Safer approach: 
            // 1. If we are loading an invoice (isEditMode && loadingInvoice), ignore.
            // 2. If user interaction, overwrite.

            // For now, let's implement a flag or check if meta address is empty.
            // For now, let's implement a flag or check if meta address is empty.

            // If we are 'loading', we shouldn't overwrite.
            // We don't have a 'loading' flag exposed for this.
            // We can check against the invoice data we just loaded.

            // Actually, simplest is: overwrite if (not edit mode) OR (edit mode AND party_id changed from original).
            // But we don't track original party_id easily here without more state.

            // Let's check if the meta address matches the party address. If not, maybe we keep it?
            // No, if user switches customer, they want that customer's address.

            // Let's protect "Edit Mode Initial Load" by checking if `form.meta.billing_address` is populated.
            // But wait, `loadInvoice` runs AFTER `customers` are loaded? 
            // If `loadInvoice` sets `form.value`, and `party_id` changes, watcher triggers.

            // Correct approach: Pause watcher during load, or use nextTick, or just check empty.
            if (!form.value.meta.billing_address.street || !isEditMode.value) {
                form.value.meta.billing_address = party.billing_address ? { ...party.billing_address } : { street: '', city: '', state: '', zip: '' }
                form.value.meta.shipping_address = party.shipping_address ? { ...party.shipping_address } : { street: '', city: '', state: '', zip: '' }

                // Also auto-enable shipping view if shipping address exists
                if (party.shipping_address?.street || party.shipping_address?.city) {
                    form.value.meta.display_options.show_shipping_address = true
                }
            }
        }
    }
})

const addItem = () => {
    form.value.items.push({
        name: '',
        description: '',
        hsn_code: '',
        quantity: 1,
        unit_price: 0,
        discount: 0,
        tax_rate: 0,
        tax_amount: 0,
        total: 0
    })
}

const removeItem = (index: number) => {
    form.value.items.splice(index, 1)
}

const calculateLineTotal = (item: InvoiceItem) => {
    const qty = Number(item.quantity) || 0
    const price = Number(item.unit_price) || 0
    const discount = Number(item.discount) || 0
    const taxRate = Number(item.tax_rate) || 0

    // Tax is usually exclusive in this simple model, i.e. Price + Tax
    // Discount is usually applied on unit price * qty
    const sub = (qty * price) - discount
    const taxable = sub > 0 ? sub : 0 // Prevent negative taxable
    const tax = taxable * (taxRate / 100)
    return taxable + tax
}

const totals = computed(() => {
    let subtotal = 0
    let cgst = 0
    let sgst = 0
    let igst = 0

    // Determine POS and Business State
    const businessState = authStore.activeBusiness?.meta?.state?.toLowerCase()
    const selectedCustomer = customers.value.find(c => c.id === form.value.party_id)
    const customerState = (selectedCustomer?.shipping_address?.state || selectedCustomer?.billing_address?.state || '').toLowerCase()

    // Default to Intra-State (CGST+SGST) if states match, else Inter-State (IGST)
    // If no state info, default to IGST to be safe or Intra? Let's assume IGST if different/missing.
    // Actually safe default is Intra if unknown usually, but logically IGST implies separate region.
    // If businessState is missing, we can't determine.

    const isInterState = businessState && customerState && businessState !== customerState
    const taxType = isInterState ? 'IGST' : 'CGST+SGST'
    const posState = customerState || 'Unknown'

    form.value.items.forEach(item => {
        const qty = Number(item.quantity) || 0
        const price = Number(item.unit_price) || 0
        const discount = Number(item.discount) || 0
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
        item.name = product.name
        // item.description = product.description || '' // Disabled as per user request (Notes logic)
        item.hsn_code = product.hsn_code || ''
        item.unit_price = Number(product.sale_price) || 0
        item.tax_rate = Number(product.tax_rate) || 0

        if (!item.quantity) item.quantity = 1
    }
}

const loadInvoice = async () => {
    if (!isEditMode.value) {
        addItem() // Add one empty row

        // Auto-fill defaults from Business Profile
        const business = authStore.activeBusiness
        if (business && business.meta) {
            form.value.notes = business.meta.default_notes || ''
            form.value.terms = business.meta.default_terms || ''
        }
        return
    }

    try {
        const invoice = await invoiceStore.fetchInvoice(route.params.id as string)
        if (invoice) {
            form.value = {
                invoice_number: invoice.invoice_number,
                party_id: invoice.party_id,
                date: invoice.date ? invoice.date.split('T')[0] : '',
                due_date: invoice.due_date ? invoice.due_date.split('T')[0] : '',
                items: invoice.items.map((i: any) => ({
                    ...i,
                    // Backfill name for legacy custom items
                    name: i.name || (!i.product_id ? i.description : '')
                })),
                notes: invoice.notes || '',
                terms: invoice.terms || '',
                challan_no: invoice.challan_no || '',
                eway_bill_no: invoice.eway_bill_no || '',
                vehicle_no: invoice.vehicle_no || '',
                po_number: invoice.po_number || '',
                meta: {
                    display_options: {
                        show_eway_details: invoice.meta?.display_options?.show_eway_details ?? false,
                        show_hsn: invoice.meta?.display_options?.show_hsn ?? true,
                        show_gst_breakdown: invoice.meta?.display_options?.show_gst_breakdown ?? true,
                        show_discount: invoice.meta?.display_options?.show_discount ?? false,
                        show_qr_bank_details: invoice.meta?.display_options?.show_qr_bank_details ?? true,
                        show_shipping_address: invoice.meta?.display_options?.show_shipping_address ?? false,
                        show_description: invoice.meta?.display_options?.show_description ?? true
                    },
                    billing_address: invoice.meta?.billing_address || { street: '', city: '', state: '', zip: '' },
                    shipping_address: invoice.meta?.shipping_address || { street: '', city: '', state: '', zip: '' }
                }
            }

            // Fallback: If meta addresses are empty (old invoice), populate from Party
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
    // Validate
    if (!form.value.party_id) {
        alert('Please select a customer')
        return
    }
    if (form.value.items.length === 0) {
        alert('Please add at least one item')
        return
    }

    // Check due date validity locally to be helpful
    if (form.value.due_date && form.value.date && new Date(form.value.due_date) < new Date(form.value.date)) {
        alert('Due Date cannot be earlier than Invoice Date')
        return
    }

    try {
        const payload = {
            ...form.value,
            // Calculate item totals explicitly for backend validation consistency
            items: form.value.items.map(item => {
                const payloadItem = { ...item }
                // Ensure name is saved for product items (legacy data fix)
                if (payloadItem.product_id && !payloadItem.name && products.value.length) {
                    const product = products.value.find(p => p.id === payloadItem.product_id)
                    if (product) {
                        payloadItem.name = product.name
                    }
                }
                return payloadItem
            })
        }

        if (isEditMode.value) {
            await invoiceStore.updateInvoice(route.params.id as string, payload)
            if (status === 'sent') {
                // If user clicked 'Save & Send', and it was a draft, we might need to finalize it.
                // Backend `update` doesn't change status to sent automatically.
                // We need a separate call if status change is desired, or update accepts status?
                // Looking at InvoiceController, update only updates fields.
                // Finalize is separate.
                await invoiceStore.finalizeInvoice(route.params.id as string)
            }
        } else {
            const newInvoice = await invoiceStore.createInvoice(payload)
            if (status === 'sent') {
                await invoiceStore.finalizeInvoice(newInvoice.id)
            }
        }
        router.push('/invoices')
    } catch (e: any) {
        console.error(e)
        const msg = e.response?.data?.message || e.message || 'Failed to save invoice'
        // If validation errors
        if (e.response?.data?.errors) {
            const errors = Object.values(e.response.data.errors).flat().join('\n')
            alert(`${msg}\n${errors}`)
        } else {
            alert(msg)
        }
    }
}

const states = ref<{ name: string, code: string }[]>([])

onMounted(async () => {
    try {
        const res = await client.get('/gst-states')
        states.value = res.data
    } catch (e) {
        console.error('Failed to load states', e)
    }

    await loadCustomers()
    await loadProducts()
    await loadInvoice()
})
</script>
