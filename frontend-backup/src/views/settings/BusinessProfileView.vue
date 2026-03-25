<template>
    <AppLayout>
        <div v-if="loading" class="text-center py-10">
            <svg class="animate-spin h-8 w-8 text-indigo-600 mx-auto" xmlns="http://www.w3.org/2000/svg" fill="none"
                viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor"
                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                </path>
            </svg>
        </div>

        <div v-else>
            <!-- Header -->
            <div class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900">Business Settings</h2>
                    <p class="text-sm text-gray-500 mt-1">
                        Manage your company details, branding, and preferences.
                    </p>
                </div>
                <div class="mt-4 sm:mt-0 flex space-x-3">
                    <button @click="save" :disabled="saving" type="button"
                        class="inline-flex items-center rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none transition-colors disabled:opacity-50">
                        <svg v-if="!saving" class="-ml-1 mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        <svg v-else class="animate-spin -ml-1 mr-2 h-5 w-5 text-white"
                            xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                            </circle>
                            <path class="opacity-75" fill="currentColor"
                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                            </path>
                        </svg>
                        {{ saving ? 'Saving...' : 'Save Changes' }}
                    </button>
                </div>
            </div>

            <div class="space-y-10 divide-y divide-gray-900/10">
                <!-- Branding Section -->
                <div class="grid grid-cols-1 gap-x-8 gap-y-8 md:grid-cols-3">
                    <div class="px-4 sm:px-0">
                        <h2 class="text-base font-semibold leading-7 text-gray-900">Branding</h2>
                        <p class="mt-1 text-sm leading-6 text-gray-600">Customize how your business appears on invoices
                            and the dashboard.</p>
                    </div>

                    <form class="bg-white shadow-sm ring-1 ring-gray-900/5 sm:rounded-xl md:col-span-2">
                        <div class="px-4 py-6 sm:p-8">
                            <div class="grid max-w-2xl grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6">
                                <!-- Logo Upload -->
                                <div class="col-span-full">
                                    <label class="block text-sm font-medium leading-6 text-gray-900">Company
                                        Logo</label>
                                    <div class="mt-2 flex items-center gap-x-3">
                                        <div v-if="form.meta.logo_url" class="relative">
                                            <img :src="form.meta.logo_url"
                                                class="h-16 w-16 rounded-full object-cover ring-1 ring-gray-200" />
                                            <button type="button" @click="removeLogo"
                                                class="absolute -top-1 -right-1 bg-red-100 rounded-full p-0.5 text-red-600 hover:bg-red-200">
                                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                                                    stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                                </svg>
                                            </button>
                                        </div>
                                        <span v-else
                                            class="h-16 w-16 overflow-hidden rounded-full bg-gray-100 ring-1 ring-gray-200 flex items-center justify-center">
                                            <svg class="h-8 w-8 text-gray-400" fill="currentColor" viewBox="0 0 24 24">
                                                <path
                                                    d="M24 20.993V24H0v-2.996A14.977 14.977 0 0112.004 15c4.904 0 9.26 2.354 11.996 5.993zM16.002 8.999a4 4 0 11-8 0 4 4 0 018 0z" />
                                            </svg>
                                        </span>

                                        <label for="logo-upload"
                                            class="rounded-md bg-white px-2.5 py-1.5 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 cursor-pointer">
                                            Change
                                            <input id="logo-upload" name="file-upload" type="file" class="sr-only"
                                                @change="handleLogoUpload" accept="image/*">
                                        </label>
                                        <span v-if="uploading" class="text-xs text-gray-500">Uploading...</span>
                                    </div>
                                </div>

                                <!-- Brand Color -->
                                <div class="sm:col-span-3">
                                    <label for="brand_color"
                                        class="block text-sm font-medium leading-6 text-gray-900">Brand Color</label>
                                    <div class="mt-2 flex items-center gap-x-2">
                                        <input type="color" v-model="form.meta.brand_color"
                                            class="h-9 w-9 border-0 p-0 rounded cursor-pointer" />
                                        <input type="text" v-model="form.meta.brand_color"
                                            class="block w-full rounded-md border-0 py-2 px-3.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6 uppercase" />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Company Details -->
                <div class="grid grid-cols-1 gap-x-8 gap-y-8 md:grid-cols-3 pt-10">
                    <div class="px-4 sm:px-0">
                        <h2 class="text-base font-semibold leading-7 text-gray-900">Company Details</h2>
                        <p class="mt-1 text-sm leading-6 text-gray-600">Information used on official documents.</p>
                    </div>

                    <form class="bg-white shadow-sm ring-1 ring-gray-900/5 sm:rounded-xl md:col-span-2">
                        <div class="px-4 py-6 sm:p-8">
                            <div class="grid max-w-2xl grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6">
                                <!-- GSTIN & PAN moved to top -->
                                <div class="sm:col-span-3">
                                    <label for="gstin" class="block text-sm font-medium leading-6 text-gray-900">
                                        GSTIN
                                        <span v-if="form.gstin && form.gstin.length >= 15"
                                            class="ml-2 inline-flex items-center rounded-md bg-green-50 px-2 py-1 text-xs font-medium text-green-700 ring-1 ring-inset ring-green-600/20">Verified</span>
                                    </label>
                                    <div class="mt-2 flex rounded-md shadow-sm">
                                        <input type="text" v-model="form.gstin" id="gstin"
                                            class="block w-full rounded-none rounded-l-md border-0 py-2 px-3.5 text-gray-900 ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6"
                                            placeholder="Ex. 29ABCDE1234F1Z5" />
                                        <button type="button" @click="fetchGst"
                                            :disabled="!form.gstin || form.gstin.length < 15 || fetchingGst"
                                            class="relative -ml-px inline-flex items-center gap-x-1.5 rounded-r-md px-3 py-2 text-sm font-semibold text-gray-900 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 disabled:opacity-50">
                                            {{ fetchingGst ? '...' : 'Fetch' }}
                                        </button>
                                    </div>
                                </div>

                                <div class="sm:col-span-3">
                                    <label for="pan" class="block text-sm font-medium leading-6 text-gray-900">
                                        PAN
                                        <span v-if="form.pan && form.pan.length >= 10"
                                            class="ml-2 inline-flex items-center rounded-md bg-green-50 px-2 py-1 text-xs font-medium text-green-700 ring-1 ring-inset ring-green-600/20">Verified</span>
                                    </label>
                                    <div class="mt-2">
                                        <input type="text" v-model="form.pan" id="pan"
                                            class="block w-full rounded-md border-0 py-2 px-3.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6"
                                            placeholder="Optional" />
                                    </div>
                                </div>

                                <div class="sm:col-span-4">
                                    <label for="name" class="block text-sm font-medium leading-6 text-gray-900">Business
                                        Name</label>
                                    <div class="mt-2">
                                        <input type="text" v-model="form.name" id="name"
                                            class="block w-full rounded-md border-0 py-2 px-3.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6" />
                                    </div>
                                </div>

                                <div class="col-span-full">
                                    <label for="address"
                                        class="block text-sm font-medium leading-6 text-gray-900">Address</label>
                                    <div class="mt-2">
                                        <textarea id="address" v-model="form.address" rows="3"
                                            class="block w-full rounded-md border-0 py-2 px-3.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6"></textarea>
                                    </div>
                                </div>

                                <div class="sm:col-span-2">
                                    <label for="pincode"
                                        class="block text-sm font-medium leading-6 text-gray-900">Pincode</label>
                                    <div class="mt-2">
                                        <input type="text" v-model="form.meta.pincode" id="pincode"
                                            class="block w-full rounded-md border-0 py-2 px-3.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6"
                                            placeholder="000000" />
                                    </div>
                                </div>

                                <div class="sm:col-span-2">
                                    <label for="city"
                                        class="block text-sm font-medium leading-6 text-gray-900">City</label>
                                    <div class="mt-2">
                                        <input type="text" v-model="form.meta.city" id="city"
                                            class="block w-full rounded-md border-0 py-2 px-3.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6" />
                                    </div>
                                </div>

                                <div class="sm:col-span-2">
                                    <label for="state"
                                        class="block text-sm font-medium leading-6 text-gray-900">State</label>
                                    <div class="mt-2">
                                        <StateSelect v-model="form.meta.state" />
                                    </div>
                                </div>

                                <div class="sm:col-span-3">
                                    <label for="website"
                                        class="block text-sm font-medium leading-6 text-gray-900">Website</label>
                                    <div class="mt-2">
                                        <input type="url" v-model="form.website" id="website"
                                            class="block w-full rounded-md border-0 py-2 px-3.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6" />
                                    </div>
                                </div>
                                <div class="sm:col-span-3">
                                    <label for="currency"
                                        class="block text-sm font-medium leading-6 text-gray-900">Currency</label>
                                    <div class="mt-2">
                                        <input type="text" v-model="form.currency" id="currency" disabled
                                            class="block w-full rounded-md border-0 py-2 px-3.5 text-gray-500 bg-gray-50 shadow-sm ring-1 ring-inset ring-gray-300 sm:text-sm sm:leading-6" />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>



                <!-- Bank Details -->
                <div class="grid grid-cols-1 gap-x-8 gap-y-8 md:grid-cols-3 pt-10">
                    <div class="px-4 sm:px-0">
                        <h2 class="text-base font-semibold leading-7 text-gray-900">Bank Details</h2>
                        <p class="mt-1 text-sm leading-6 text-gray-600">These details will be shown on invoices if
                            enabled.</p>
                    </div>

                    <form class="bg-white shadow-sm ring-1 ring-gray-900/5 sm:rounded-xl md:col-span-2">
                        <div class="px-4 py-6 sm:p-8">
                            <div class="grid max-w-2xl grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6">
                                <div class="col-span-full">
                                    <label for="bank_name"
                                        class="block text-sm font-medium leading-6 text-gray-900">Bank Name</label>
                                    <div class="mt-2">
                                        <input type="text" v-model="form.bank_name" id="bank_name"
                                            class="block w-full rounded-md border-0 py-2 px-3.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6" />
                                    </div>
                                </div>

                                <div class="col-span-full">
                                    <label for="account_number"
                                        class="block text-sm font-medium leading-6 text-gray-900">Account Number</label>
                                    <div class="mt-2">
                                        <input type="text" v-model="form.account_number" id="account_number"
                                            class="block w-full rounded-md border-0 py-2 px-3.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6" />
                                    </div>
                                </div>

                                <div class="sm:col-span-3">
                                    <label for="ifsc_code"
                                        class="block text-sm font-medium leading-6 text-gray-900">IFSC Code</label>
                                    <div class="mt-2">
                                        <input type="text" v-model="form.ifsc_code" id="ifsc_code"
                                            class="block w-full rounded-md border-0 py-2 px-3.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6" />
                                    </div>
                                </div>

                                <div class="sm:col-span-3">
                                    <label for="upi_id" class="block text-sm font-medium leading-6 text-gray-900">UPI ID
                                        (for QR Code)</label>
                                    <div class="mt-2">
                                        <input type="text" v-model="form.meta.upi_id" id="upi_id"
                                            placeholder="e.g. business@upi"
                                            class="block w-full rounded-md border-0 py-2 px-3.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6" />
                                    </div>
                                </div>

                                <div class="sm:col-span-3">
                                    <label for="holder_name"
                                        class="block text-sm font-medium leading-6 text-gray-900">Account Holder
                                        Name</label>
                                    <div class="mt-2">
                                        <input type="text" v-model="form.meta.account_holder_name" id="holder_name"
                                            class="block w-full rounded-md border-0 py-2 px-3.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6" />
                                    </div>
                                </div>

                                <div class="col-span-full">
                                    <div class="relative flex gap-x-3">
                                        <div class="flex h-6 items-center">
                                            <input id="show_bank_details" v-model="form.meta.show_bank_details"
                                                type="checkbox"
                                                class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-600">
                                        </div>
                                        <div class="text-sm leading-6">
                                            <label for="show_bank_details" class="font-medium text-gray-900">Show
                                                details on invoice</label>
                                            <p class="text-gray-500">Disable this to hide bank details even if they are
                                                filled.</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Invoice Defaults -->
                <div class="grid grid-cols-1 gap-x-8 gap-y-8 md:grid-cols-3 pt-10">
                    <div class="px-4 sm:px-0">
                        <h2 class="text-base font-semibold leading-7 text-gray-900">Invoice Defaults</h2>
                        <p class="mt-1 text-sm leading-6 text-gray-600">These will appear on every new invoice.</p>
                    </div>

                    <form class="bg-white shadow-sm ring-1 ring-gray-900/5 sm:rounded-xl md:col-span-2">
                        <div class="px-4 py-6 sm:p-8">
                            <div class="grid max-w-2xl grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6">
                                <div class="col-span-full">
                                    <label for="default_notes"
                                        class="block text-sm font-medium leading-6 text-gray-900">Default Notes</label>
                                    <div class="mt-2">
                                        <textarea id="default_notes" v-model="form.meta.default_notes" rows="3"
                                            class="block w-full rounded-md border-0 py-2 px-3.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6"></textarea>
                                    </div>
                                </div>

                                <div class="col-span-full">
                                    <label for="default_terms"
                                        class="block text-sm font-medium leading-6 text-gray-900">Default Terms &
                                        Conditions</label>
                                    <div class="mt-2">
                                        <textarea id="default_terms" v-model="form.meta.default_terms" rows="3"
                                            class="block w-full rounded-md border-0 py-2 px-3.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6"></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Invoice Settings (Layout) -->
                <div class="grid grid-cols-1 gap-x-8 gap-y-8 md:grid-cols-3 pt-10">
                    <div class="px-4 sm:px-0">
                        <h2 class="text-base font-semibold leading-7 text-gray-900">Invoice Settings</h2>
                        <p class="mt-1 text-sm leading-6 text-gray-600">Customize the look and feel of your invoices.
                        </p>
                    </div>

                    <form class="bg-white shadow-sm ring-1 ring-gray-900/5 sm:rounded-xl md:col-span-2">
                        <div class="px-4 py-6 sm:p-8">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                                <!-- Default Layout Card -->
                                <div class="relative border-2 rounded-xl p-4 cursor-pointer transition-all hover:border-gray-300"
                                    :class="form.meta.invoice_layout === 'default' ? 'border-indigo-600 ring-1 ring-indigo-600 bg-indigo-50/10' : 'border-gray-200'"
                                    @click="form.meta.invoice_layout = 'default'">
                                    <div class="flex justify-between items-start mb-4">
                                        <div class="flex items-center gap-2">
                                            <div class="h-4 w-4 rounded-full border flex items-center justify-center bg-white"
                                                :class="form.meta.invoice_layout === 'default' ? 'border-indigo-600' : 'border-gray-300'">
                                                <div v-if="form.meta.invoice_layout === 'default'"
                                                    class="h-2 w-2 rounded-full bg-indigo-600"></div>
                                            </div>
                                            <span class="font-bold text-gray-900 text-sm">Default (Simple)</span>
                                        </div>
                                        <span
                                            class="text-xs bg-gray-100 text-gray-600 px-2 py-0.5 rounded-full">Free</span>
                                    </div>
                                    <div class="space-y-2 opacity-50 pointer-events-none select-none">
                                        <div class="h-2 w-1/3 bg-gray-200 rounded"></div>
                                        <div class="h-2 w-full bg-gray-100 rounded"></div>
                                        <div class="h-2 w-full bg-gray-100 rounded"></div>
                                    </div>
                                    <button type="button" @click.stop="openPreview('default')"
                                        class="mt-4 w-full py-1.5 text-xs font-semibold text-indigo-600 bg-indigo-50 hover:bg-indigo-100 rounded-lg transition-colors">
                                        Preview Layout
                                    </button>
                                </div>

                                <!-- Professional Layout Card -->
                                <div class="relative border-2 rounded-xl p-4 transition-all" :class="[
                                    form.meta.invoice_layout === 'professional' ? 'border-indigo-600 ring-1 ring-indigo-600 bg-indigo-50/10' : 'border-gray-200',
                                    !canUsePremiumLayouts ? 'opacity-75 grayscale bg-gray-50' : 'cursor-pointer hover:border-gray-300'
                                ]" @click="canUsePremiumLayouts ? form.meta.invoice_layout = 'professional' : null">
                                    <div v-if="!canUsePremiumLayouts" class="absolute top-2 right-2 z-10">
                                        <span
                                            class="bg-gray-900 text-white text-[10px] font-bold px-2 py-0.5 rounded flex items-center gap-1 shadow-sm">
                                            <svg class="w-3 h-3 text-purple-400" fill="currentColor"
                                                viewBox="0 0 20 20">
                                                <path fill-rule="evenodd"
                                                    d="M10 1a4.5 4.5 0 0 0-4.5 4.5V9H5a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2v-6a2 2 0 0 0-2-2h-.5V5.5A4.5 4.5 0 0 0 10 1Zm3 8V5.5a3 3 0 1 0-6 0V9h6Z"
                                                    clip-rule="evenodd" />
                                            </svg>
                                            ENTERPRISE
                                        </span>
                                    </div>

                                    <div class="flex justify-between items-start mb-4">
                                        <div class="flex items-center gap-2">
                                            <div class="h-4 w-4 rounded-full border flex items-center justify-center bg-white"
                                                :class="form.meta.invoice_layout === 'professional' ? 'border-indigo-600' : 'border-gray-300'">
                                                <div v-if="form.meta.invoice_layout === 'professional'"
                                                    class="h-2 w-2 rounded-full bg-indigo-600"></div>
                                            </div>
                                            <span class="font-bold text-gray-900 text-sm">Professional</span>
                                        </div>
                                    </div>
                                    <div class="space-y-2 opacity-50 pointer-events-none select-none">
                                        <div class="flex gap-2">
                                            <div class="h-8 w-8 bg-gray-200 rounded"></div>
                                            <div class="space-y-1 flex-1">
                                                <div class="h-2 w-1/2 bg-gray-200 rounded"></div>
                                                <div class="h-2 w-1/4 bg-gray-100 rounded"></div>
                                            </div>
                                        </div>
                                        <div class="h-10 bg-gray-50 border border-gray-100 rounded mt-2"></div>
                                    </div>
                                    <button type="button" @click.stop="openPreview('professional')"
                                        class="mt-4 w-full py-1.5 text-xs font-semibold text-indigo-600 bg-indigo-50 hover:bg-indigo-100 rounded-lg transition-colors">
                                        Preview Layout
                                    </button>
                                </div>

                                <!-- Grid Premium Layout Card -->
                                <div class="relative border-2 rounded-xl p-4 transition-all" :class="[
                                    form.meta.invoice_layout === 'grid_premium' ? 'border-indigo-600 ring-1 ring-indigo-600 bg-indigo-50/10' : 'border-gray-200',
                                    !canUsePremiumLayouts ? 'opacity-75 grayscale bg-gray-50' : 'cursor-pointer hover:border-gray-300'
                                ]" @click="canUsePremiumLayouts ? form.meta.invoice_layout = 'grid_premium' : null">
                                    <div v-if="!canUsePremiumLayouts" class="absolute top-2 right-2 z-10">
                                        <span
                                            class="bg-gray-900 text-white text-[10px] font-bold px-2 py-0.5 rounded flex items-center gap-1 shadow-sm">
                                            <svg class="w-3 h-3 text-purple-400" fill="currentColor"
                                                viewBox="0 0 20 20">
                                                <path fill-rule="evenodd"
                                                    d="M10 1a4.5 4.5 0 0 0-4.5 4.5V9H5a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2v-6a2 2 0 0 0-2-2h-.5V5.5A4.5 4.5 0 0 0 10 1Zm3 8V5.5a3 3 0 1 0-6 0V9h6Z"
                                                    clip-rule="evenodd" />
                                            </svg>
                                            ENTERPRISE
                                        </span>
                                    </div>

                                    <div class="flex justify-between items-start mb-4">
                                        <div class="flex items-center gap-2">
                                            <div class="h-4 w-4 rounded-full border flex items-center justify-center bg-white"
                                                :class="form.meta.invoice_layout === 'grid_premium' ? 'border-indigo-600' : 'border-gray-300'">
                                                <div v-if="form.meta.invoice_layout === 'grid_premium'"
                                                    class="h-2 w-2 rounded-full bg-indigo-600"></div>
                                            </div>
                                            <span class="font-bold text-gray-900 text-sm">Grid Premium</span>
                                        </div>
                                    </div>
                                    <div
                                        class="space-y-2 opacity-50 pointer-events-none select-none border border-gray-300 p-1">
                                        <!-- A more grid-like skeleton -->
                                        <div class="flex border-b border-gray-300 pb-1 gap-2">
                                            <div class="h-4 w-4 bg-gray-200"></div>
                                            <div class="flex-1 space-y-1">
                                                <div class="h-1.5 w-1/2 bg-gray-200"></div>
                                                <div class="h-1.5 w-1/3 bg-gray-100"></div>
                                            </div>
                                        </div>
                                        <div class="grid grid-cols-4 gap-0.5 mt-1">
                                            <div class="h-2 bg-gray-100 col-span-3"></div>
                                            <div class="h-2 bg-gray-200"></div>
                                        </div>
                                    </div>
                                    <button type="button" @click.stop="openPreview('grid_premium')"
                                        class="mt-4 w-full py-1.5 text-xs font-semibold text-indigo-600 bg-indigo-50 hover:bg-indigo-100 rounded-lg transition-colors">
                                        Preview Layout
                                    </button>
                                </div>

                                <!-- Classic Grid Layout Card -->
                                <div class="relative border-2 rounded-xl p-4 transition-all" :class="[
                                    form.meta.invoice_layout === 'classic' ? 'border-indigo-600 ring-1 ring-indigo-600 bg-indigo-50/10' : 'border-gray-200',
                                    !canUseClassicLayout ? 'opacity-75 grayscale bg-gray-50' : 'cursor-pointer hover:border-gray-300'
                                ]" @click="canUseClassicLayout ? form.meta.invoice_layout = 'classic' : null">
                                    <div v-if="!canUseClassicLayout" class="absolute top-2 right-2 z-10">
                                        <span
                                            class="bg-gray-900 text-white text-[10px] font-bold px-2 py-0.5 rounded flex items-center gap-1 shadow-sm">
                                            <svg class="w-3 h-3 text-orange-400" fill="currentColor"
                                                viewBox="0 0 20 20">
                                                <path fill-rule="evenodd"
                                                    d="M10 1a4.5 4.5 0 0 0-4.5 4.5V9H5a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2v-6a2 2 0 0 0-2-2h-.5V5.5A4.5 4.5 0 0 0 10 1Zm3 8V5.5a3 3 0 1 0-6 0V9h6Z"
                                                    clip-rule="evenodd" />
                                            </svg>
                                            ENTERPRISE+
                                        </span>
                                    </div>

                                    <div class="flex justify-between items-start mb-4">
                                        <div class="flex items-center gap-2">
                                            <div class="h-4 w-4 rounded-full border flex items-center justify-center bg-white"
                                                :class="form.meta.invoice_layout === 'classic' ? 'border-indigo-600' : 'border-gray-300'">
                                                <div v-if="form.meta.invoice_layout === 'classic'"
                                                    class="h-2 w-2 rounded-full bg-indigo-600"></div>
                                            </div>
                                            <span class="font-bold text-gray-900 text-sm">Classic Grid (Tax
                                                Invoice)</span>
                                        </div>
                                        <span
                                            class="text-xs bg-green-100 text-green-700 font-bold px-2 py-0.5 rounded-full">New</span>
                                    </div>
                                    <div
                                        class="space-y-2 opacity-50 pointer-events-none select-none border border-black p-1">
                                        <!-- Grid Simulation -->
                                        <div class="border-b border-black pb-1 mb-1 text-[8px] font-bold text-center">
                                            TAX INVOICE</div>
                                        <div class="grid grid-cols-2 gap-0 border-b border-black">
                                            <div class="h-4 border-r border-black bg-gray-100"></div>
                                            <div class="h-4 bg-gray-50"></div>
                                        </div>
                                        <div class="grid grid-cols-4 gap-0 mt-1 h-6 bg-gray-50 border border-black">
                                        </div>
                                    </div>
                                    <button type="button" @click.stop="openPreview('classic')"
                                        class="mt-4 w-full py-1.5 text-xs font-semibold text-indigo-600 bg-indigo-50 hover:bg-indigo-100 rounded-lg transition-colors">
                                        Preview Layout
                                    </button>
                                </div>

                                <!-- Half Page Layout Card -->
                                <div class="relative border-2 rounded-xl p-4 transition-all" :class="[
                                    form.meta.invoice_layout === 'half_page' ? 'border-indigo-600 ring-1 ring-indigo-600 bg-indigo-50/10' : 'border-gray-200',
                                    !canUseHalfPage ? 'opacity-75 grayscale bg-gray-50' : 'cursor-pointer hover:border-gray-300'
                                ]" @click="canUseHalfPage ? form.meta.invoice_layout = 'half_page' : null">
                                    <div v-if="!canUseHalfPage" class="absolute top-2 right-2 z-10">
                                        <span
                                            class="bg-gray-900 text-white text-[10px] font-bold px-2 py-0.5 rounded flex items-center gap-1 shadow-sm">
                                            <svg class="w-3 h-3 text-yellow-400" fill="currentColor"
                                                viewBox="0 0 20 20">
                                                <path
                                                    d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                            </svg>
                                            PRO
                                        </span>
                                    </div>
                                    <div class="flex justify-between items-start mb-4">
                                        <div class="flex items-center gap-2">
                                            <div class="h-4 w-4 rounded-full border flex items-center justify-center bg-white"
                                                :class="form.meta.invoice_layout === 'half_page' ? 'border-indigo-600' : 'border-gray-300'">
                                                <div v-if="form.meta.invoice_layout === 'half_page'"
                                                    class="h-2 w-2 rounded-full bg-indigo-600"></div>
                                            </div>
                                            <span class="font-bold text-gray-900 text-sm">Half Page</span>
                                        </div>
                                        <span
                                            class="text-xs bg-green-100 text-green-700 font-bold px-2 py-0.5 rounded-full">Paper
                                            Saver</span>
                                    </div>
                                    <!-- Half page sketch -->
                                    <div class="opacity-50 pointer-events-none select-none border border-gray-300 p-1"
                                        style="height: 60px; overflow:hidden">
                                        <div class="flex justify-between items-start mb-0.5">
                                            <div class="space-y-0.5">
                                                <div class="h-2 w-20 bg-gray-700 rounded-sm"></div>
                                                <div class="h-1 w-14 bg-gray-200 rounded-sm"></div>
                                                <div class="h-1 w-10 bg-gray-200 rounded-sm"></div>
                                            </div>
                                            <div class="text-right space-y-0.5">
                                                <div class="h-1 w-12 bg-gray-100 rounded-sm ml-auto"></div>
                                                <div class="h-2 w-16 bg-gray-300 rounded-sm ml-auto"></div>
                                            </div>
                                        </div>
                                        <div class="border-t border-gray-200 mt-0.5 pt-0.5 grid grid-cols-4 gap-0.5">
                                            <div class="h-1 bg-gray-300 col-span-2"></div>
                                            <div class="h-1 bg-gray-200"></div>
                                            <div class="h-1 bg-gray-200"></div>
                                            <div class="h-1 bg-gray-50 col-span-2"></div>
                                            <div class="h-1 bg-gray-50"></div>
                                            <div class="h-1 bg-gray-100"></div>
                                        </div>
                                    </div>
                                    <p class="text-[10px] text-gray-500 mt-2 text-center">Full width · Half height · All
                                        data</p>
                                    <button type="button" @click.stop="openPreview('half_page')"
                                        class="mt-4 w-full py-1.5 text-xs font-semibold text-indigo-600 bg-indigo-50 hover:bg-indigo-100 rounded-lg transition-colors">
                                        Preview Layout
                                    </button>
                                </div>

                            </div>
                        </div>
                    </form>
                </div>

                <!-- App Appearance -->
                <div class="grid grid-cols-1 gap-x-8 gap-y-8 md:grid-cols-3 pt-10">
                    <div class="px-4 sm:px-0">
                        <h2 class="text-base font-semibold leading-7 text-gray-900">App Appearance</h2>
                        <p class="mt-1 text-sm leading-6 text-gray-600">Customize how the app looks on this device.
                            These settings are saved locally in your browser.</p>
                    </div>

                    <div class="bg-white shadow-sm ring-1 ring-gray-900/5 sm:rounded-xl md:col-span-2">
                        <div class="px-4 py-6 sm:p-8 space-y-8">

                            <!-- Font Scale -->
                            <div>
                                <label class="block text-sm font-semibold text-gray-800 mb-1">UI Zoom / Font
                                    Size</label>
                                <p class="text-xs text-gray-500 mb-3">Smaller = more data on screen without scrolling
                                </p>
                                <div class="flex flex-wrap gap-2">
                                    <button v-for="scale in SCALE_OPTIONS" :key="scale" type="button"
                                        @click="setScale(scale)"
                                        class="px-4 py-2 rounded-lg text-sm font-medium border-2 transition-all" :class="uiScale === scale
                                            ? 'border-indigo-600 bg-indigo-50 text-indigo-700 shadow-sm'
                                            : 'border-gray-200 text-gray-600 hover:border-gray-300 hover:bg-gray-50'">
                                        {{ scale === 100 ? `${scale}% (Default)` : `${scale}%` }}
                                    </button>
                                </div>
                                <p class="mt-2 text-xs text-gray-400">Current: <strong>{{ uiScale }}%</strong> — changes
                                    apply instantly.</p>
                            </div>

                            <!-- Theme Color -->
                            <div>
                                <label class="block text-sm font-semibold text-gray-800 mb-1">App Theme Color</label>
                                <p class="text-xs text-gray-500 mb-3">Accent color for sidebar active links, buttons &
                                    badges</p>
                                <div class="flex flex-wrap gap-3">
                                    <button v-for="theme in THEME_OPTIONS" :key="theme.id" type="button"
                                        @click="setTheme(theme.id)" :title="theme.label"
                                        class="relative h-9 w-9 rounded-full transition-all ring-offset-2" :class="[
                                            theme.bg,
                                            themeColor === theme.id ? 'ring-2 ring-offset-2 scale-110' : 'hover:scale-105'
                                        ]"
                                        :style="themeColor === theme.id ? `box-shadow: 0 0 0 3px ${theme.primaryHex}` : ''">
                                        <svg v-if="themeColor === theme.id" class="h-5 w-5 text-white mx-auto"
                                            fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
                                                d="M5 13l4 4L19 7" />
                                        </svg>
                                    </button>
                                </div>
                                <p class="mt-2 text-xs text-gray-400">Current: <strong class="capitalize">{{ themeColor
                                }}</strong></p>
                            </div>

                            <!-- Reset -->
                            <div class="pt-2 border-t border-gray-100">
                                <button type="button" @click="resetAppearance"
                                    class="text-xs text-gray-500 hover:text-red-600 transition-colors flex items-center gap-1">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                    </svg>
                                    Reset to default (100%, Indigo)
                                </button>
                            </div>

                        </div>
                    </div>
                </div>

                <!-- Data Management -->
                <div class="grid grid-cols-1 gap-x-8 gap-y-8 md:grid-cols-3 pt-10">
                    <div class="px-4 sm:px-0">
                        <h2 class="text-base font-semibold leading-7 text-gray-900">Data Management</h2>
                        <p class="mt-1 text-sm leading-6 text-gray-600">Export your business data.</p>
                    </div>

                    <div class="md:col-span-2 space-y-8">
                        <div class="bg-white shadow-sm ring-1 ring-gray-900/5 sm:rounded-xl">
                            <div class="px-4 py-6 sm:p-8">
                                <div class="max-w-xl">
                                    <h3 class="text-sm font-semibold leading-6 text-gray-900">Import Data (Tally XML)
                                    </h3>
                                    <p class="mt-1 text-sm text-gray-500">Migrate from Tally. Export your Masters
                                        (Ledgers &
                                        Items) as XML and upload here.</p>

                                    <div class="mt-4">
                                        <div class="flex items-center gap-3">
                                            <input type="file" ref="fileInput" accept=".xml,.txt"
                                                @change="handleFileSelect"
                                                class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100" />

                                            <button @click="uploadTallyXML" :disabled="!selectedFile || importing"
                                                class="rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600 disabled:opacity-50 disabled:cursor-not-allowed">
                                                <span v-if="importing">Importing...</span>
                                                <span v-else>Import</span>
                                            </button>
                                        </div>

                                        <!-- Import Result Stats -->
                                        <div v-if="importStats" class="mt-4 p-3 bg-gray-50 rounded-md text-xs">
                                            <h4 class="font-bold text-gray-700 mb-1">Import Results:</h4>
                                            <div class="grid grid-cols-2 gap-2">
                                                <p><span class="font-medium">Ledgers Created:</span> {{
                                                    importStats.ledgers_created }}</p>
                                                <p><span class="font-medium">Ledgers Updated:</span> {{
                                                    importStats.ledgers_updated }}</p>
                                                <p><span class="font-medium">Items Created:</span> {{
                                                    importStats.stock_items_created }}</p>
                                                <p><span class="font-medium">Items Updated:</span> {{
                                                    importStats.stock_items_updated }}</p>
                                            </div>
                                            <div v-if="importStats.errors && importStats.errors.length > 0"
                                                class="mt-2 text-red-600">
                                                <p class="font-bold">Errors ({{ importStats.errors.length }}):</p>
                                                <ul class="list-disc pl-4 max-h-24 overflow-y-auto">
                                                    <li v-for="(err, i) in importStats.errors" :key="i">{{ err }}</li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="bg-white shadow-sm ring-1 ring-gray-900/5 sm:rounded-xl">
                            <div class="px-4 py-6 sm:p-8">
                                <div class="max-w-xl">
                                    <h3 class="text-sm font-semibold leading-6 text-gray-900">Backup Data (CSV)</h3>
                                    <p class="mt-1 text-sm text-gray-500">Download your data in CSV format for Excel or
                                        Google Drive backup.</p>

                                    <div class="mt-4 flex flex-col sm:flex-row gap-3">
                                        <button @click="downloadCsv('invoices')"
                                            :disabled="downloadingCsv === 'invoices'"
                                            class="inline-flex items-center justify-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 disabled:opacity-50">
                                            <svg class="mr-2 h-4 w-4 text-green-600" fill="none" viewBox="0 0 24 24"
                                                stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                            </svg>
                                            Invoices.csv
                                        </button>
                                        <button @click="downloadCsv('invoice_items')"
                                            :disabled="downloadingCsv === 'invoice_items'"
                                            class="inline-flex items-center justify-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 disabled:opacity-50">
                                            <svg class="mr-2 h-4 w-4 text-green-600" fill="none" viewBox="0 0 24 24"
                                                stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                            </svg>
                                            Invoice Items.csv
                                        </button>
                                        <button @click="downloadCsv('parties')" :disabled="downloadingCsv === 'parties'"
                                            class="inline-flex items-center justify-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 disabled:opacity-50">
                                            <svg class="mr-2 h-4 w-4 text-green-600" fill="none" viewBox="0 0 24 24"
                                                stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                            </svg>
                                            Customers.csv
                                        </button>
                                        <button @click="downloadCsv('expenses')"
                                            :disabled="downloadingCsv === 'expenses'"
                                            class="inline-flex items-center justify-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 disabled:opacity-50">
                                            <svg class="mr-2 h-4 w-4 text-green-600" fill="none" viewBox="0 0 24 24"
                                                stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                            </svg>
                                            Expenses.csv
                                        </button>
                                    </div>
                                </div>

                                <hr class="my-6 border-gray-100" />

                                <div class="max-w-2xl">
                                    <h3 class="text-sm font-semibold leading-6 text-gray-900">Full System Export (JSON)
                                    </h3>
                                    <p class="mt-1 text-sm text-gray-500 mb-4">Download a complete raw data dump for
                                        migration or deep analysis.</p>
                                    <button @click="downloadData" :disabled="downloading" type="button"
                                        class="inline-flex items-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 disabled:opacity-50">
                                        <svg v-if="!downloading" class="-ml-0.5 mr-1.5 h-5 w-5 text-gray-400"
                                            xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                            stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                        </svg>
                                        <svg v-else class="animate-spin -ml-0.5 mr-1.5 h-5 w-5 text-gray-400"
                                            xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                                stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor"
                                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                            </path>
                                        </svg>
                                        {{ downloading ? 'Exporting...' : 'Export JSON Dump' }}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>


        <!-- Layout Preview Modal -->
        <div v-if="showPreviewModal" class="relative z-50" aria-labelledby="modal-title" role="dialog"
            aria-modal="true">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"></div>
            <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
                <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                    <div
                        class="relative transform overflow-hidden rounded-lg bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-5xl">
                        <div class="absolute right-0 top-0 hidden pr-4 pt-4 sm:block z-10">
                            <button type="button"
                                class="rounded-md bg-white text-gray-400 hover:text-gray-500 focus:outline-none"
                                @click="showPreviewModal = false">
                                <span class="sr-only">Close</span>
                                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                        <div
                            class="bg-gray-100 px-4 pb-4 pt-5 sm:p-0 sm:pb-0 h-[85vh] flex flex-col sm:flex-row overflow-hidden">
                            <!-- Sidebar Controls -->
                            <div
                                class="w-full sm:w-64 bg-white border-r border-gray-200 p-6 overflow-y-auto shrink-0 z-20 shadow-lg sm:shadow-none">
                                <div class="mb-6">
                                    <h3 class="text-base font-semibold leading-6 text-gray-900 mb-1">Preview Options
                                    </h3>
                                    <p class="text-xs text-gray-500">Toggle options to see how the invoice adapts.
                                    </p>
                                </div>

                                <div class="space-y-4">
                                    <div class="relative flex items-start">
                                        <div class="flex h-6 items-center">
                                            <input id="prev_hsn" type="checkbox" v-model="previewControls.show_hsn"
                                                class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-600">
                                        </div>
                                        <div class="ml-3 text-sm leading-6">
                                            <label for="prev_hsn" class="font-medium text-gray-900">Show
                                                HSN/SAC</label>
                                        </div>
                                    </div>

                                    <div class="relative flex items-start">
                                        <div class="flex h-6 items-center">
                                            <input id="prev_discount" type="checkbox"
                                                v-model="previewControls.show_discount"
                                                class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-600">
                                        </div>
                                        <div class="ml-3 text-sm leading-6">
                                            <label for="prev_discount" class="font-medium text-gray-900">Show
                                                Discount</label>
                                        </div>
                                    </div>

                                    <div class="relative flex items-start">
                                        <div class="flex h-6 items-center">
                                            <input id="prev_gst" type="checkbox"
                                                v-model="previewControls.show_gst_breakdown"
                                                class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-600">
                                        </div>
                                        <div class="ml-3 text-sm leading-6">
                                            <label for="prev_gst" class="font-medium text-gray-900">GST
                                                Breakdown</label>
                                        </div>
                                    </div>

                                    <div class="relative flex items-start">
                                        <div class="flex h-6 items-center">
                                            <input id="prev_shipping" type="checkbox"
                                                v-model="previewControls.show_shipping_address"
                                                class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-600">
                                        </div>
                                        <div class="ml-3 text-sm leading-6">
                                            <label for="prev_shipping" class="font-medium text-gray-900">Shipping
                                                Address</label>
                                        </div>
                                    </div>

                                    <div class="relative flex items-start">
                                        <div class="flex h-6 items-center">
                                            <input id="prev_eway" type="checkbox"
                                                v-model="previewControls.show_eway_details"
                                                class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-600">
                                        </div>
                                        <div class="ml-3 text-sm leading-6">
                                            <label for="prev_eway" class="font-medium text-gray-900">Transport
                                                Details</label>
                                        </div>
                                    </div>

                                    <div class="relative flex items-start">
                                        <div class="flex h-6 items-center">
                                            <input id="prev_bank" type="checkbox"
                                                v-model="previewControls.show_qr_bank_details"
                                                class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-600">
                                        </div>
                                        <div class="ml-3 text-sm leading-6">
                                            <label for="prev_bank" class="font-medium text-gray-900">Bank &
                                                QR</label>
                                        </div>
                                    </div>

                                    <!-- GST SCENARIO TOGGLE -->
                                    <div class="pt-4 mt-4 border-t border-gray-200">
                                        <h4 class="text-xs font-bold text-gray-500 uppercase tracking-widest mb-2">
                                            GST
                                            Scenario</h4>
                                        <div class="space-y-2">
                                            <div class="flex items-center">
                                                <input id="gst_inter" name="gst_scenario" type="radio" value="inter"
                                                    v-model="previewControls.gst_scenario"
                                                    class="h-4 w-4 border-gray-300 text-indigo-600 focus:ring-indigo-600">
                                                <label for="gst_inter"
                                                    class="ml-3 block text-xs font-medium text-gray-900">Inter-State
                                                    (IGST)</label>
                                            </div>
                                            <div class="flex items-center">
                                                <input id="gst_intra" name="gst_scenario" type="radio" value="intra"
                                                    v-model="previewControls.gst_scenario"
                                                    class="h-4 w-4 border-gray-300 text-indigo-600 focus:ring-indigo-600">
                                                <label for="gst_intra"
                                                    class="ml-3 block text-xs font-medium text-gray-900">Intra-State
                                                    (CGST+SGST)</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="mt-8 pt-6 border-t border-gray-200">
                                    <p class="text-xs text-gray-500 mb-4">You are viewing the <span class="font-bold">
                                            {{ previewLayout === 'professional' ? 'Professional' : (previewLayout
                                                ===
                                                'grid_premium' ? 'Grid Premium' : 'Default') }}
                                        </span> layout.</p>
                                    <button type="button"
                                        class="w-full rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50"
                                        @click="showPreviewModal = false">
                                        Close Preview
                                    </button>
                                </div>
                            </div>

                            <!-- Main Preview Area -->
                            <div class="flex-1 bg-gray-100 overflow-y-auto p-4 sm:p-8 relative">
                                <div class="flex justify-center mb-4 sticky top-0 z-10">
                                    <h3
                                        class="text-sm font-semibold text-gray-600 bg-white/90 backdrop-blur px-4 py-1.5 rounded-full shadow-sm ring-1 ring-gray-200">
                                        Live Preview Mode
                                    </h3>
                                </div>
                                <div
                                    class="transform scale-[0.65] origin-top sm:scale-[0.85] md:scale-100 transition-transform duration-300 shadow-2xl rounded-sm">
                                    <component :is="previewComponent" :invoice="dummyInvoice"
                                        :taxBreakdown="dummyTaxBreakdown" :qrCodeUrl="dummyQrCode" />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import { ref, onMounted, watch, computed } from 'vue'
import AppLayout from '../../layouts/AppLayout.vue'
import client from '../../api/client'
import { useAuthStore } from '../../stores/auth'
import { useGeneralStore } from '../../stores/general'
import { storeToRefs } from 'pinia'
import { fetchPincodeDetails } from '../../services/PincodeService'
import StateSelect from '../../components/StateSelect.vue'
import { useAppearance } from '../../composables/useAppearance'

// Layouts for Preview
import DefaultLayout from '../invoices/layouts/DefaultLayout.vue'
import ProfessionalLayout from '../invoices/layouts/ProfessionalLayout.vue'
import GridPremiumLayout from '../invoices/layouts/GridPremiumLayout.vue'
import ClassicGridLayout from '../invoices/layouts/ClassicGridLayout.vue'

const authStore = useAuthStore()
const generalStore = useGeneralStore()
const { states } = storeToRefs(generalStore)
const { uiScale, themeColor, SCALE_OPTIONS, THEME_OPTIONS, setScale, setTheme, resetAppearance } = useAppearance()

const loading = ref(true)
const saving = ref(false)
const uploading = ref(false)
const downloading = ref(false)
const downloadingCsv = ref<string | null>(null)
const fetchingGst = ref(false)

const showPreviewModal = ref(false)
const previewLayout = ref<'default' | 'professional' | 'grid_premium' | 'classic' | 'half_page'>('default')

// ── Plan Tier Helpers ──────────────────────────────────────────────
// Layout access matrix:
//   Free / Starter        → Default (Simple) only
//   Pro                   → Default + Half Page (Paper Saver)
//   Enterprise            → Default + Professional + Grid Premium + Half Page
//   Enterprise Business   → ALL (+ Classic Grid)
const planSlug = computed(() => authStore.currentSubscription?.plan?.slug || 'free')

const canUseHalfPage = computed(() =>
    ['pro', 'enterprise', 'enterprise_business'].includes(planSlug.value)
)
const canUsePremiumLayouts = computed(() =>   // Professional + Grid Premium
    ['enterprise', 'enterprise_business'].includes(planSlug.value)
)
const canUseClassicLayout = computed(() =>    // Classic Grid — top tier only
    planSlug.value === 'enterprise_business'
)

import HalfPageLayout from '../invoices/layouts/HalfPageLayout.vue'

const previewComponent = computed(() => {
    if (previewLayout.value === 'professional') return ProfessionalLayout
    if (previewLayout.value === 'grid_premium') return GridPremiumLayout
    if (previewLayout.value === 'classic') return ClassicGridLayout
    if (previewLayout.value === 'half_page') return HalfPageLayout
    return DefaultLayout
})

const previewControls = ref({
    show_hsn: true,
    show_gst_breakdown: true,
    show_discount: true,
    show_shipping_address: true,
    show_eway_details: true,
    show_qr_bank_details: true,
    gst_scenario: 'inter' as 'inter' | 'intra' // Default to Inter-State (IGST) as requested
})

const openPreview = (layout: 'default' | 'professional' | 'grid_premium' | 'classic' | 'half_page') => {
    previewLayout.value = layout
    // Initialize preview controls
    previewControls.value = {
        show_hsn: true,
        show_gst_breakdown: true,
        show_discount: true,
        show_shipping_address: true,
        show_eway_details: true,
        show_qr_bank_details: form.value.meta.show_bank_details,
        gst_scenario: 'inter'
    }
    showPreviewModal.value = true
}

// Reactive dummy data based on GST Scenario
const dummyBusinessState = computed(() => form.value.meta.state || 'Uttar Pradesh')

const dummyCustomerState = computed(() => {
    if (previewControls.value.gst_scenario === 'intra') {
        return dummyBusinessState.value // Same state = CGST+SGST
    }
    // Different state = IGST. If business is Maharashtra, use UP, else Maharashtra.
    return dummyBusinessState.value === 'Maharashtra' ? 'Uttar Pradesh' : 'Maharashtra'
})

const dummyTaxBreakdown = computed(() => {
    const isInterState = previewControls.value.gst_scenario === 'inter'
    return {
        cgst: isInterState ? 0 : 450 + 81, // 9% of 5000 + 9% of 900 (approx logic based on items) -> actually items tax amounts are hardcoded.
        sgst: isInterState ? 0 : 450 + 81,
        igst: isInterState ? 1062 : 0,
        taxType: isInterState ? 'IGST' : 'CGST+SGST',
        posState: dummyCustomerState.value
    }
})

const dummyInvoice = computed(() => ({
    invoice_number: 'INV-2024-001',
    date: new Date().toISOString(),
    due_date: new Date(Date.now() + 7 * 24 * 60 * 60 * 1000).toISOString(),
    grand_total: 6962,
    subtotal: 6000,
    tax_total: 1062,
    items: [
        { id: 1, description: 'Web Design Services', hsn_code: '9983', quantity: 1, unit_price: 5000, discount: 0, tax_rate: 18, tax_amount: 900, total: 5900 },
        { id: 2, description: 'Domain Registration', hsn_code: '9983', quantity: 1, unit_price: 1000, discount: 100, tax_rate: 18, tax_amount: 162, total: 1062 }
    ],
    terms: form.value.meta.default_terms || 'Payment due within 15 days.',
    notes: form.value.meta.default_notes || 'Thanks for your business.',
    challan_no: 'CH-123',
    eway_bill_no: '123456789012',
    vehicle_no: 'MH-12-AB-1234',
    po_number: 'PO-2024-005',
    party: {
        name: 'Acme Corp Pvt Ltd',
        gstin: '27ABCDE1234F1Z5',
        billing_address: { street: '45 Business Park', city: 'Mumbai', state: dummyCustomerState.value, zip: '400001' },
        shipping_address: { street: '12 Warehouseline', city: 'Pune', state: dummyCustomerState.value, zip: '411001' }
    },
    business: {
        name: form.value.name || 'Your Business Name',
        address: form.value.address || '123 Main St, Tech Park',
        gstin: form.value.gstin || '29ABCDE1234F1Z5',
        bank_name: form.value.bank_name || 'HDFC Bank',
        account_number: form.value.account_number || '123456789012',
        ifsc_code: form.value.ifsc_code || 'HDFC0001234',
        mobile: '9876543210',
        website: form.value.website || 'www.example.com',
        meta: { ...form.value.meta, upi_id: form.value.meta.upi_id || 'business@upi' }
    },
    meta: {
        display_options: {
            show_hsn: previewControls.value.show_hsn,
            show_gst_breakdown: previewControls.value.show_gst_breakdown,
            show_discount: previewControls.value.show_discount,
            show_shipping_address: previewControls.value.show_shipping_address,
            show_eway_details: previewControls.value.show_eway_details,
            show_qr_bank_details: previewControls.value.show_qr_bank_details
        },
        billing_address: { street: '45 Business Park', city: 'Mumbai', state: dummyCustomerState.value, zip: '400001' },
        shipping_address: { street: '12 Warehouseline', city: 'Pune', state: dummyCustomerState.value, zip: '411001' }
    }
}))

// Use a placeholder QR code for preview
const dummyQrCode = 'https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=upi://pay?pa=test@upi&pn=TestBox'

const form = ref({
    name: '',
    address: '',
    website: '',
    currency: '',
    gstin: '',
    pan: '',
    bank_name: '',
    account_number: '',
    ifsc_code: '',
    meta: {
        logo_url: '',
        brand_color: '#4F46E5', // Default Indigo
        theme_preference: 'light',
        pincode: '',
        city: '',
        state: '',
        upi_id: '',
        default_notes: '',
        default_terms: '',
        account_holder_name: '',
        show_bank_details: false,
        invoice_layout: 'default' as 'default' | 'professional' | 'grid_premium' | 'classic' | 'half_page'
    }
})

const fetchBusiness = async () => {
    try {
        generalStore.fetchStates()

        // Assuming current business is selected and available
        const businessId = authStore.activeBusiness?.id;
        if (!businessId) return;

        const response = await client.get(`/businesses/${businessId}`)
        const business = response.data;

        form.value = {
            name: business.name,
            address: business.address || '',
            website: business.website || '',
            currency: business.currency,
            gstin: business.gstin || '',
            pan: business.pan || '',
            bank_name: business.bank_name || '',
            account_number: business.account_number || '',
            ifsc_code: business.ifsc_code || '',
            meta: {
                logo_url: business.meta?.logo_url || '',
                brand_color: business.meta?.brand_color || '#4F46E5',
                theme_preference: business.meta?.theme_preference || 'light',
                pincode: business.meta?.pincode || '',
                city: business.meta?.city || '',
                state: business.meta?.state || '',
                upi_id: business.meta?.upi_id || '',
                default_notes: business.meta?.default_notes || '',
                default_terms: business.meta?.default_terms || '',
                account_holder_name: business.meta?.account_holder_name || '',
                show_bank_details: business.meta?.show_bank_details || false,
                invoice_layout: business.meta?.invoice_layout || 'default'
            }
        }
    } catch (e) {
        console.error("Failed to load business details", e)
    } finally {
        loading.value = false
    }
}

const fetchGst = async () => {
    if (!form.value.gstin || form.value.gstin.length < 15) return
    fetchingGst.value = true
    try {
        const response = await client.get(`/gst-lookup/${form.value.gstin}`)
        const data = response.data

        if (data.legal_name) form.value.name = data.legal_name
        if (data.trade_name && !form.value.name) form.value.name = data.trade_name

        // Auto-extract PAN from GSTIN if 15 chars (chars 3-12)
        if (form.value.gstin.length === 15) {
            form.value.pan = form.value.gstin.substring(2, 12)
        }

        if (data.address) form.value.address = data.address

        // Attempt to map granular fields if available (from raw meta)
        const raw = data.raw || data // Handle both cached (raw in meta) and fresh (data is meta)

        if (raw) {
            // Construct detailed address string
            let addrParts = []
            const fullAddr = data.full_address_details || {}

            // Prioritize verified granular details
            if (Object.keys(fullAddr).length > 0) {
                if (fullAddr.floor_no) addrParts.push(fullAddr.floor_no)
                if (fullAddr.building_no) addrParts.push(fullAddr.building_no)
                if (fullAddr.building_name) addrParts.push(fullAddr.building_name)
                if (fullAddr.street) addrParts.push(fullAddr.street)
                if (fullAddr.location) addrParts.push(fullAddr.location)
                // if (fullAddr.district && fullAddr.district !== fullAddr.city) addrParts.push(fullAddr.district)
            }
            // Fallback to raw fields if full_address_details is empty but raw exists (e.g. old data)
            else {
                if (raw.flno) addrParts.push(raw.flno)
                if (raw.bno) addrParts.push(raw.bno)
                if (raw.bnm) addrParts.push(raw.bnm)
                if (raw.st) addrParts.push(raw.st)
                if (raw.loc) addrParts.push(raw.loc)
            }

            if (addrParts.length > 0) {
                form.value.address = addrParts.filter(Boolean).join('\n')
            }

            // City/State/Pincode Mappings
            if (data.city) form.value.meta.city = data.city
            else if (raw.city) form.value.meta.city = raw.city

            if (data.pincode) form.value.meta.pincode = data.pincode
            else if (raw.pincode) form.value.meta.pincode = raw.pincode

            // State
            const stateName = data.state || raw.state
            if (stateName) {
                // Try to match state name 
                const matchedState = states.value.find(s => s.name.toLowerCase() === stateName.toLowerCase())
                form.value.meta.state = matchedState ? matchedState.name : stateName
            }
        }

        alert('Details fetched successfully!')
    } catch (e: any) {
        alert('Failed to fetch GST details: ' + (e.response?.data?.message || e.message))
    } finally {
        fetchingGst.value = false
    }
}

watch(() => form.value.meta.pincode, async (newPincode) => {
    if (newPincode && newPincode.length === 6) {
        const details = await fetchPincodeDetails(newPincode)
        if (details) {
            form.value.meta.city = details.city

            // Find matching state
            const matchedState = states.value.find(s => s.name.toLowerCase() === details.state.toLowerCase())
            if (matchedState) {
                form.value.meta.state = matchedState.name
            } else {
                form.value.meta.state = details.state
            }
        }
    }
})

const handleLogoUpload = async (event: Event) => {
    const file = (event.target as HTMLInputElement).files?.[0]
    if (!file) return;

    uploading.value = true;
    const formData = new FormData();
    formData.append('file', file);

    try {
        const response = await client.post('/upload', formData, {
            headers: { 'Content-Type': 'multipart/form-data' }
        });

        // Update local state immediately
        form.value.meta.logo_url = response.data.url;
    } catch (e) {
        alert('Failed to upload logo.');
    } finally {
        uploading.value = false;
    }
}

const removeLogo = () => {
    form.value.meta.logo_url = '';
}

const save = async () => {
    saving.value = true;
    try {
        const businessId = authStore.activeBusiness?.id;
        const response = await client.put(`/businesses/${businessId}`, form.value);

        // Preserve pivot data from current business before updating
        const currentPivot = authStore.activeBusiness?.pivot;
        const updatedBusiness = {
            ...response.data,
            pivot: currentPivot || response.data.pivot // Preserve existing pivot or use response pivot
        };

        authStore.setActiveBusiness(updatedBusiness);

        // Should trigger a refresh or toast notification
        alert('Settings saved successfully!');
    } catch (e) {
        alert('Failed to save settings.');
    } finally {
        saving.value = false;
    }
}

// Tally Import Logic
const selectedFile = ref<File | null>(null)
const importing = ref(false)
const importStats = ref<any>(null)

const handleFileSelect = (event: Event) => {
    const target = event.target as HTMLInputElement
    if (target.files && target.files.length > 0) {
        selectedFile.value = target.files[0] as File
        importStats.value = null // Reset stats
    }
}

const uploadTallyXML = async () => {
    if (!selectedFile.value) return

    importing.value = true
    importStats.value = null

    const formData = new FormData()
    formData.append('file', selectedFile.value)

    try {
        const response = await client.post('/import/tally', formData, {
            headers: {
                'Content-Type': 'multipart/form-data'
            }
        })
        importStats.value = response.data.stats
        alert('Import process completed. Check results below.')

        // Refresh business data to reflect potential updates? 
        // Not strictly necessary as we imported other entities, but good practice if we were viewing them.

    } catch (e: any) {
        alert('Import failed: ' + (e.response?.data?.message || e.message))
    } finally {
        importing.value = false
        // Clear file input
        selectedFile.value = null
        // Note: We can't easily clear the file input DOM element value directly without a ref, 
        // but the user can see the status map.
    }
}

const downloadData = async () => {
    downloading.value = true
    try {
        const response = await client.get('/export')
        const blob = new Blob([JSON.stringify(response.data, null, 2)], { type: 'application/json' })
        const url = window.URL.createObjectURL(blob)
        const link = document.createElement('a')
        link.href = url
        link.setAttribute('download', `backup-${new Date().toISOString().split('T')[0]}.json`)
        document.body.appendChild(link)
        link.click()
        document.body.removeChild(link)
    } catch (e) {
        alert('Failed to export data')
    } finally {
        downloading.value = false
    }
}

const downloadCsv = async (type: string) => {
    downloadingCsv.value = type
    try {
        const response = await client.get(`/export/${type}`, { responseType: 'blob' })
        const url = window.URL.createObjectURL(new Blob([response.data]))
        const link = document.createElement('a')
        link.href = url
        link.setAttribute('download', `${type}-${new Date().toISOString().split('T')[0]}.csv`)
        document.body.appendChild(link)
        link.click()
        document.body.removeChild(link)
    } catch (e) {
        alert(`Failed to export ${type} CSV`)
    } finally {
        downloadingCsv.value = null
    }
}

onMounted(() => {
    fetchBusiness();
})
</script>
