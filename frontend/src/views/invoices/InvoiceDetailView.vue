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

        <div v-else-if="invoice" class="max-w-4xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
            <!-- Action Bar -->
            <!-- Back Link -->


            <!-- Header Row 1: Title and Back Link -->
            <div class="mb-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 print:hidden">
                <div class="flex items-center gap-4">
                    <router-link :to="backRoute"
                        class="text-gray-500 hover:text-gray-700 p-1 rounded-full hover:bg-gray-100 transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                    </router-link>

                    <h1 class="text-2xl font-bold text-gray-900 flex items-center gap-3">
                        {{ typeLabel }} {{ invoice.invoice_number }}
                        <span :class="getStatusClass(invoice.status)"
                            class="inline-flex items-center rounded-md px-2.5 py-1 text-sm font-medium ring-1 ring-inset">
                            {{ capitalize(invoice.status) }}
                        </span>
                    </h1>
                </div>
            </div>

            <!-- Header Row 2: Action Buttons -->
            <div
                class="mb-8 flex items-center gap-2 print:hidden overflow-x-auto pb-2 -mx-4 px-4 sm:mx-0 sm:px-0 sm:overflow-visible sm:flex-wrap sm:justify-end scrollbar-hide">
                <router-link :to="editRoute" v-if="invoice.status === 'draft'"
                    class="inline-flex items-center justify-center rounded-lg bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 whitespace-nowrap">Edit</router-link>

                <!-- Convert to Invoice (Quotes only) -->
                <button v-if="invoice.type === 'quote'" @click="convertToInvoice" :disabled="converting"
                    class="inline-flex items-center justify-center rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-500 gap-1.5 disabled:opacity-50 whitespace-nowrap">
                    <svg v-if="converting" class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                        </circle>
                        <path class="opacity-75" fill="currentColor"
                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                        </path>
                    </svg>
                    Convert to Invoice
                </button>

                <!-- Duplicate button -->
                <button v-if="invoice.status !== 'draft'" @click="duplicateInvoice"
                    class="inline-flex items-center justify-center rounded-lg bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 gap-1.5 whitespace-nowrap">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-500" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                    </svg>
                    Duplicate
                </button>

                <!-- Copy Type Selection (Only for Invoice Types that support copies) -->
                <div v-if="invoice.type === 'invoice' || invoice.type === 'tax_invoice' || invoice.type === 'bill_of_supply'"
                    class="relative inline-block text-left">
                    <select v-model="printCopyType"
                        class="inline-flex items-center justify-center rounded-lg bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="original">Original</option>
                        <option value="duplicate">Duplicate</option>
                        <option value="triplicate">Triplicate</option>
                    </select>
                </div>

                <button @click="printInvoice"
                    class="inline-flex items-center justify-center rounded-lg bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50">
                    Print
                </button>

                <button v-if="invoice.status !== 'draft' && invoice.type === 'invoice'" @click="createCreditNote"
                    class="inline-flex items-center justify-center rounded-lg bg-white px-4 py-2 text-sm font-medium text-red-700 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-red-50 gap-1.5 whitespace-nowrap">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-red-500" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6" />
                    </svg>
                    Return / Credit Note
                </button>

                <button @click="downloadPdf" :disabled="downloading"
                    class="inline-flex items-center justify-center rounded-lg bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 gap-1.5 whitespace-nowrap">
                    <svg v-if="downloading" class="animate-spin h-4 w-4 text-gray-500"
                        xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                        </circle>
                        <path class="opacity-75" fill="currentColor"
                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                        </path>
                    </svg>
                    PDF
                </button>

                <button @click="sendEmail" :disabled="sendingEmail"
                    class="inline-flex items-center justify-center rounded-lg bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 gap-1.5 whitespace-nowrap">
                    <svg v-if="sendingEmail" class="animate-spin h-4 w-4 text-gray-500"
                        xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                        </circle>
                        <path class="opacity-75" fill="currentColor"
                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                        </path>
                    </svg>
                    {{ invoice.type === 'quote' ? 'Email Quote' : 'Email Invoice' }}
                </button>

                <!-- Remind / Share Button -->
                <div v-if="invoice.type === 'invoice'" class="relative inline-block text-left">
                    <button @click="shareMenuOpen = !shareMenuOpen"
                        class="inline-flex items-center justify-center rounded-lg bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 gap-1.5 whitespace-nowrap">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-500" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z" />
                        </svg>
                        Remind
                    </button>
                    <!-- Share Menu Dropdown -->
                    <div v-if="shareMenuOpen"
                        class="absolute right-0 z-10 mt-2 w-48 origin-top-right rounded-md bg-white shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none">
                        <div class="py-1">
                            <button @click="shareWhatsApp"
                                class="block w-full px-4 py-2 text-left text-sm text-gray-700 hover:bg-gray-100 flex items-center gap-2">
                                <svg class="h-4 w-4 text-green-500" fill="currentColor" viewBox="0 0 24 24">
                                    <path
                                        d="M.057 24l1.687-6.163c-3.104-5.391-.039-12.01 6.163-15.038 6.136-2.992 12.871-.161 14.773 6.126 1.902 6.287-1.127 12.718-7.396 14.623L.057 24z" />
                                </svg>
                                WhatsApp
                            </button>
                            <button @click="shareSMS"
                                class="block w-full px-4 py-2 text-left text-sm text-gray-700 hover:bg-gray-100 flex items-center gap-2">
                                <svg class="h-4 w-4 text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                                </svg>
                                SMS
                            </button>
                        </div>
                    </div>
                </div>

                <button v-if="invoice.status === 'draft' && invoice.type !== 'quote'" @click="finalize"
                    class="inline-flex items-center justify-center rounded-lg bg-green-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-green-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-green-600 whitespace-nowrap">
                    Finalize
                </button>
                <button
                    v-if="invoice.status !== 'draft' && invoice.status !== 'paid' && invoice.status !== 'void' && invoice.type === 'invoice'"
                    @click="showPaymentModal = true"
                    class="inline-flex items-center justify-center rounded-lg bg-green-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-green-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-green-600 whitespace-nowrap">
                    Record Payment
                </button>
            </div>

            <!-- Payment History Section -->
            <div v-if="invoice.allocations && invoice.allocations.length > 0" class="mb-8 border-b pb-8 print:hidden">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Payment History</h2>
                <div class="overflow-hidden shadow ring-1 ring-black ring-opacity-5 rounded-lg">
                    <table class="min-w-full divide-y divide-gray-300">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col"
                                    class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-6">Date
                                </th>
                                <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">
                                    Reference</th>
                                <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Method
                                </th>
                                <th scope="col" class="px-3 py-3.5 text-right text-sm font-semibold text-gray-900">
                                    Amount</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white">
                            <tr v-for="alloc in invoice.allocations" :key="alloc.id">
                                <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-6">
                                    {{ alloc.payment.date }}</td>
                                <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">{{ alloc.payment.reference
                                    || '-' }}</td>
                                <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500 capitalize">{{
                                    alloc.payment.method.replace('_', ' ') }}</td>
                                <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500 text-right">₹{{
                                    Number(alloc.amount).toFixed(2) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Related Credit Notes Section -->
            <div v-if="invoice.credit_notes && invoice.credit_notes.length > 0" class="mb-8 border-b pb-8 print:hidden">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Related Credit Notes</h2>
                <div class="overflow-hidden shadow ring-1 ring-black ring-opacity-5 rounded-lg">
                    <table class="min-w-full divide-y divide-gray-300">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col"
                                    class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-6">
                                    Number
                                </th>
                                <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">
                                    Date
                                </th>
                                <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">
                                    Reason
                                </th>
                                <th scope="col" class="px-3 py-3.5 text-right text-sm font-semibold text-gray-900">
                                    Amount
                                </th>
                                <th scope="col" class="relative py-3.5 pl-3 pr-4 sm:pr-6">
                                    <span class="sr-only">View</span>
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white">
                            <tr v-for="cn in invoice.credit_notes" :key="cn.id">
                                <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-6">
                                    {{ cn.invoice_number }}
                                </td>
                                <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                    {{ cn.date }}
                                </td>
                                <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                    {{ cn.reason || '-' }}
                                </td>
                                <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500 text-right">
                                    ₹{{ Number(cn.grand_total).toFixed(2) }}
                                </td>
                                <td
                                    class="relative whitespace-nowrap py-4 pl-3 pr-4 text-right text-sm font-medium sm:pr-6">
                                    <router-link :to="`/credit-notes/${cn.id}`"
                                        class="text-indigo-600 hover:text-indigo-900">
                                        View
                                    </router-link>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Dynamic Layout Render -->
            <!-- Dynamic Layout Render -->
            <div ref="containerRef"
                class="invoice-container w-full flex justify-center pb-8 overflow-hidden bg-gray-100/50 pt-4">
                <div :style="{ height: scaledHeight + 'px', width: scaledWidth + 'px' }"
                    class="invoice-scale-wrapper relative transition-all duration-200">
                    <div ref="contentRef"
                        class="invoice-content-wrapper origin-top-left absolute top-0 left-0 bg-white shadow-lg flex flex-col items-center"
                        :style="{ transform: `scale(${scale})`, width: '210mm', minHeight: '297mm' }">
                        <component :is="layoutComponent" :invoice="invoice" :taxBreakdown="taxBreakdown"
                            :qrCodeUrl="qrCodeUrl" :copyType="printCopyType" class="box-border" />
                    </div>
                </div>
            </div>



            <!-- Record Payment Modal -->
            <div v-if="showPaymentModal" class="relative z-50" aria-labelledby="modal-title" role="dialog"
                aria-modal="true">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"></div>

                <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
                    <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                        <div
                            class="relative transform overflow-hidden rounded-lg bg-white px-4 pb-4 pt-5 text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg sm:p-6">
                            <div>
                                <div
                                    class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-green-100">
                                    <svg class="h-6 w-6 text-green-600" fill="none" viewBox="0 0 24 24"
                                        stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 4.5h.75zm0 15a.75.75 0 01.75-.75h-.75V15c.996-.264 2.131.46 2.62.872l1.32.768c.379.22.844.186 1.18-.088l1.753-1.344c.435-.333 1.055-.262 1.465.166l1.786 1.84a1.125 1.125 0 001.62-.005l1.09-1.125c.343-.353.948-.28 1.25.127l.654.887c.184.25.467.397.776.402 1.353.023 2.802-.303 3.93-1.077m0-9.67l-.768-1.554a1.737 1.737 0 00-2.316-.76l-8.08 4.296a1.166 1.166 0 00-.51 1.472l1.309 2.923a.857.857 0 001.5.088l2.97-4.137" />
                                    </svg>
                                </div>
                                <div class="mt-3 text-center sm:mt-5">
                                    <h3 class="text-base font-semibold leading-6 text-gray-900" id="modal-title">Record
                                        Payment</h3>
                                    <div class="mt-2">
                                        <p class="text-sm text-gray-500">Record a payment received for Invoice {{
                                            invoice?.invoice_number }}.</p>
                                    </div>

                                    <!-- Form -->
                                    <div class="mt-4 space-y-4 text-left">
                                        <div>
                                            <label class="block text-sm font-medium leading-6 text-gray-900">Amount
                                                Received (₹)</label>
                                            <div class="mt-2">
                                                <input type="number" step="0.01" v-model="paymentForm.amount"
                                                    class="block w-full rounded-md border-0 py-2 px-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6"
                                                    required>
                                            </div>
                                            <p class="mt-1 text-xs text-gray-500">Outstanding: ₹{{ outstandingAmount }}
                                            </p>
                                        </div>

                                        <div>
                                            <label class="block text-sm font-medium leading-6 text-gray-900">Payment
                                                Date</label>
                                            <div class="mt-2">
                                                <input type="date" v-model="paymentForm.date"
                                                    class="block w-full rounded-md border-0 py-2 px-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
                                            </div>
                                        </div>

                                        <div>
                                            <label class="block text-sm font-medium leading-6 text-gray-900">Payment
                                                Method</label>
                                            <div class="mt-2">
                                                <select v-model="paymentForm.method"
                                                    class="block w-full rounded-md border-0 py-2 px-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
                                                    <option value="cash">Cash</option>
                                                    <option value="bank_transfer">Bank Transfer</option>
                                                    <option value="upi">UPI</option>
                                                    <option value="check">Check</option>
                                                    <option value="card">Card</option>
                                                    <option value="other">Other</option>
                                                </select>
                                            </div>
                                        </div>

                                        <div>
                                            <label class="block text-sm font-medium leading-6 text-gray-900">Reference /
                                                Notes</label>
                                            <div class="mt-2">
                                                <input type="text" v-model="paymentForm.reference"
                                                    placeholder="e.g. Transaction ID, Check No."
                                                    class="block w-full rounded-md border-0 py-2 px-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="mt-5 sm:mt-6 sm:grid sm:grid-flow-row-dense sm:grid-cols-2 sm:gap-3">
                                <button type="button" @click="submitPayment" :disabled="submittingPayment"
                                    class="inline-flex w-full justify-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600 sm:col-start-2 disabled:opacity-50">
                                    {{ submittingPayment ? 'Saving...' : 'Record Payment' }}
                                </button>
                                <button type="button" @click="showPaymentModal = false"
                                    class="mt-3 inline-flex w-full justify-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 sm:col-start-1 sm:mt-0">Cancel</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </AppLayout>

    <ConfirmationModal :is-open="confirmModal.isOpen" :title="confirmModal.title" :message="confirmModal.message"
        :confirm-text="confirmModal.confirmText" :processing="confirmModal.processing" @close="closeConfirmModal"
        @confirm="handleConfirm" />

    <AlertModal :is-open="alertModal.isOpen" :title="alertModal.title" :message="alertModal.message"
        :type="alertModal.type" @close="alertModal.isOpen = false" />
</template>

<script setup lang="ts">
import { ref, onMounted, onUnmounted, computed, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import AppLayout from '../../layouts/AppLayout.vue'
import { useInvoiceStore } from '../../stores/invoice'
import { storeToRefs } from 'pinia'
import DefaultLayout from './layouts/DefaultLayout.vue'
import ProfessionalLayout from './layouts/ProfessionalLayout.vue'
import GridPremiumLayout from './layouts/GridPremiumLayout.vue'
import ClassicGridLayout from './layouts/ClassicGridLayout.vue'
import client from '../../api/client'

// @ts-ignore
import QRCode from 'qrcode'
// @ts-ignore
import html2pdf from 'html2pdf.js'

import ConfirmationModal from '../../components/ConfirmationModal.vue'
import AlertModal from '../../components/AlertModal.vue'

const invoice = ref<any>(null)
const loading = ref(true)
const invoiceStore = useInvoiceStore()
const route = useRoute()
const router = useRouter()

const printCopyType = ref('original')

// Modal States
const confirmModal = ref({
    isOpen: false,
    title: '',
    message: '',
    confirmText: 'Confirm',
    processing: false,
    onConfirm: async () => { }
})

const alertModal = ref({
    isOpen: false,
    title: '',
    message: '',
    type: 'info'
})

const showAlert = (message: string, title = 'Info', type = 'info') => {
    alertModal.value = {
        isOpen: true,
        title,
        message,
        type
    }
}

const showConfirm = (title: string, message: string, onConfirm: () => Promise<void>, confirmText = 'Confirm') => {
    confirmModal.value = {
        isOpen: true,
        title,
        message,
        confirmText,
        processing: false,
        onConfirm
    }
}

const closeConfirmModal = () => {
    confirmModal.value.isOpen = false
}

const handleConfirm = async () => {
    confirmModal.value.processing = true
    try {
        await confirmModal.value.onConfirm()
        closeConfirmModal()
    } catch (e) {
        console.error(e)
    } finally {
        confirmModal.value.processing = false
    }
}

// Destructure from storeToRefs after local refs are defined
const { currentInvoice: storeInvoice, loading: storeLoading } = storeToRefs(invoiceStore)

const capitalize = (s: string) => {
    if (typeof s !== 'string') return ''
    return s.charAt(0).toUpperCase() + s.slice(1)
}

// Watch store values and update local refs
watch(storeInvoice, (newVal) => {
    invoice.value = newVal
    if (newVal?.invoice_number) {
        document.title = newVal.invoice_number
    }
}, { immediate: true })

watch(storeLoading, (newVal) => {
    loading.value = newVal
}, { immediate: true })


const converting = ref(false)
const containerRef = ref<HTMLElement | null>(null)
const contentRef = ref<HTMLElement | null>(null)
const scale = ref(1)
const scaledHeight = ref(1123) // Default A4 px height
const scaledWidth = ref(794) // Default A4 px width

const updateScale = () => {
    if (!containerRef.value || !contentRef.value) return
    const padding = 24 // px (horiz padding)
    const availableWidth = Math.min(window.innerWidth - padding, containerRef.value.clientWidth)

    // A4 width in px (210mm approx 794px)
    const contentOriginalWidth = 794

    // Calculate Scale
    const newScale = availableWidth < contentOriginalWidth ? availableWidth / contentOriginalWidth : 1
    scale.value = newScale

    // Update dimensions
    // We strictly use the contentRef scrollHeight to determine actual height needed
    const originalHeight = contentRef.value.scrollHeight || 1123
    scaledHeight.value = originalHeight * newScale
    scaledWidth.value = contentOriginalWidth * newScale
}

let resizeObserver: ResizeObserver | null = null

onMounted(() => {
    window.addEventListener('resize', updateScale)
    // Initial calls
    setTimeout(updateScale, 100)
    setTimeout(updateScale, 500)
})

onUnmounted(() => {
    window.removeEventListener('resize', updateScale)
    if (resizeObserver) resizeObserver.disconnect()
})

// Watch for contentRef to appear (after loading)
watch(contentRef, (el) => {
    if (el) {
        if (resizeObserver) resizeObserver.disconnect()
        resizeObserver = new ResizeObserver(() => {
            updateScale()
        })
        resizeObserver.observe(el)
        // Trigger immediate update
        updateScale()
    }
})

const backRoute = computed(() => {
    if (!invoice.value) return '/invoices'
    if (invoice.value.type === 'credit_note') return '/credit-notes'
    return '/invoices'
})

const editRoute = computed(() => {
    if (!invoice.value) return ''
    if (invoice.value.type === 'quote') return `/quotations/${invoice.value.id}/edit`
    if (invoice.value.type === 'credit_note') return `/credit-notes/${invoice.value.id}/edit`
    return `/invoices/${invoice.value.id}/edit`
})

const typeLabel = computed(() => {
    if (!invoice.value) return 'Invoice'
    if (invoice.value.type === 'quote') return 'Estimate'
    if (invoice.value.type === 'credit_note') return 'Credit Note'
    return 'Invoice'
})

const convertToInvoice = async () => {
    if (!invoice.value) return
    if (invoice.value.status !== 'accepted' && invoice.value.status !== 'draft' && invoice.value.status !== 'sent') {
        showAlert('Only accepted, draft or sent estimates can be converted to invoices', 'Cannot Convert', 'error')
        return
    }

    showConfirm(
        'Convert to Invoice',
        'This will create a new Draft Invoice from this Estimate. Continue?',
        async () => {
            try {
                const newInvoice = await invoiceStore.convertEstimateToInvoice(invoice.value.id)
                showAlert('Estimate converted to Invoice successfully!', 'Success', 'success')
                router.push(`/invoices/${newInvoice.id}/edit`)
            } catch (e: any) {
                showAlert(e.response?.data?.message || 'Failed to convert estimate', 'Error', 'error')
            }
        },
        'Convert'
    )
}

const layoutComponent = computed(() => {
    const layout = invoice.value?.business?.meta?.invoice_layout || 'default'
    if (layout === 'professional') return ProfessionalLayout
    if (layout === 'grid_premium') return GridPremiumLayout
    if (layout === 'classic') return ClassicGridLayout
    return DefaultLayout
})

const taxBreakdown = computed(() => {
    if (!invoice.value) return { cgst: 0, sgst: 0, igst: 0, taxType: '', posState: '', cgstRate: 0, sgstRate: 0, igstRate: 0, hsnGroups: [] }

    let cgst = 0
    let sgst = 0
    let igst = 0
    let totalTaxRate = 0
    let itemCount = 0
    const hsnMap: { [key: string]: any } = {}

    const businessState = invoice.value.business?.meta?.state?.toLowerCase()
    const customer = invoice.value.party
    const customerState = (customer?.shipping_address?.state || customer?.billing_address?.state || '').toLowerCase()

    const isInterState = businessState && customerState && businessState !== customerState
    const taxType = isInterState ? 'IGST' : 'CGST+SGST';

    (invoice.value.items || []).forEach((item: any) => {
        const lineTax = Number(item.tax_amount) || 0
        const itemTaxRate = Number(item.tax_rate) || 0
        const itemCess = Number(item.cess_amount) || 0
        const itemCessRate = Number(item.cess_rate) || 0
        const hsn = item.hsn_code || item.product?.hsn_code || '-'
        const taxableAmount = Number(item.total) || 0

        if (itemTaxRate > 0) {
            totalTaxRate += itemTaxRate
            itemCount++
        }

        if (isInterState) {
            igst += lineTax
        } else {
            cgst += lineTax / 2
            sgst += lineTax / 2
        }

        // Group by HSN
        if (!hsnMap[hsn]) {
            hsnMap[hsn] = {
                hsn,
                taxable: 0,
                cgst_rate: isInterState ? 0 : itemTaxRate / 2,
                sgst_rate: isInterState ? 0 : itemTaxRate / 2,
                igst_rate: isInterState ? itemTaxRate : 0,
                cess_rate: itemCessRate,
                cgst_amount: 0,
                sgst_amount: 0,
                igst_amount: 0,
                cess_amount: 0,
                total_tax: 0
            }
        }

        const group = hsnMap[hsn]
        group.taxable += taxableAmount

        if (isInterState) {
            group.igst_amount += lineTax
        } else {
            group.cgst_amount += lineTax / 2
            group.sgst_amount += lineTax / 2
        }
        group.cess_amount += itemCess
        group.total_tax += (lineTax + itemCess)
    })

    const avgTaxRate = itemCount > 0 ? totalTaxRate / itemCount : 0
    const cgstRate = isInterState ? 0 : avgTaxRate / 2
    const sgstRate = isInterState ? 0 : avgTaxRate / 2
    const igstRate = isInterState ? avgTaxRate : 0

    return {
        cgst,
        sgst,
        igst,
        taxType,
        posState: customer?.shipping_address?.state || customer?.billing_address?.state || 'N/A',
        cgstRate,
        sgstRate,
        igstRate,
        hsnGroups: Object.values(hsnMap)
    }
})



const showPaymentModal = ref(false)
const submittingPayment = ref(false)
const payToday = new Date().toISOString().split('T')[0]

const paymentForm = ref({
    amount: 0,
    date: payToday,
    method: 'bank_transfer',
    reference: ''
})

const outstandingAmount = computed(() => {
    if (!invoice.value) return 0
    const paidByAllocations = (invoice.value.allocations || []).reduce((sum: number, a: any) => sum + Number(a.amount), 0)
    // Or use paid_amount from invoice if updated? Let's use allocations sum for now as it's realtime.
    return (Number(invoice.value.grand_total) - paidByAllocations).toFixed(2)
})

const submitPayment = async () => {
    if (!invoice.value) return

    submittingPayment.value = true
    try {
        await invoiceStore.recordPayment(invoice.value.id, {
            amount: Number(paymentForm.value.amount),
            payment_date: paymentForm.value.date,
            payment_method: paymentForm.value.method,
            notes: paymentForm.value.reference // Use reference as notes for simplicity or add notes field
        })

        // Reload invoice to show updated status/payments
        await loadInvoice()
        showPaymentModal.value = false

        // Reset form
        paymentForm.value = {
            amount: 0,
            date: new Date().toISOString().split('T')[0],
            method: 'bank_transfer',
            reference: ''
        }
        showAlert('Payment recorded successfully', 'Success', 'success')
    } catch (e: any) {
        console.error('Payment failed', e)
        showAlert(e.response?.data?.message || 'Failed to record payment', 'Error', 'error')
    } finally {
        submittingPayment.value = false
    }
}

// Watch invoice to pre-fill remaining amount
watch(() => showPaymentModal.value, (val) => {
    if (val && invoice.value) {
        paymentForm.value.amount = Number(outstandingAmount.value)
    }
})

const qrCodeUrl = ref('')

const generateQR = async () => {
    if (!invoice.value?.business?.meta?.upi_id) return

    const upiId = invoice.value.business.meta.upi_id
    const payName = invoice.value.business.name.replace(/\s/g, '+') // Simple encoding
    const amount = Math.round(invoice.value.grand_total)

    // Construct UPI URL
    // Standard: upi://pay?pa=<id>&pn=<name>&am=<amount>&cu=INR
    const upiUrl = `upi://pay?pa=${upiId}&pn=${payName}&am=${amount}&cu=INR`

    try {
        qrCodeUrl.value = await QRCode.toDataURL(upiUrl)
    } catch (err) {
        console.error('QR Gen Error', err)
    }
}

const loadInvoice = async () => {
    try {
        await invoiceStore.fetchInvoice(route.params.id as string)
        if (invoice.value) {
            await generateQR()
        }
    } catch (e) {
        console.error(e)
    }
}

const printInvoice = () => {
    // 1. Create or get print container
    let printContainer = document.getElementById('print-container')
    if (!printContainer) {
        printContainer = document.createElement('div')
        printContainer.id = 'print-container'
        document.body.appendChild(printContainer)
    }

    // 2. Clone content
    if (contentRef.value) {
        printContainer.innerHTML = '' // Clear previous
        // Deep clone the invoice content
        const clone = contentRef.value.cloneNode(true) as HTMLElement

        // Remove any utility classes that might conflict or use specific classes for print
        // For now, we trust the clone. We might need to ensure the clone loses 'transform' if set inline.
        clone.style.transform = 'none'
        clone.style.width = '100%'
        clone.style.height = 'auto'
        clone.style.minHeight = '0'
        clone.style.boxShadow = 'none'
        clone.style.margin = '0'
        clone.style.position = 'static'
        clone.style.overflow = 'visible'

        printContainer.appendChild(clone)
    }

    // 3. Print
    window.print()

    // 4. Cleanup (Delay to ensure print dialog captures it, though window.print blocks in some browsers, in others it doesn't)
    // Actually, keeping it hidden in DOM is fine, but clearing it after might be safer to save memory.
    // cleanup happens on next print or we can leave it.
    // We will leave it for stability.
}

const finalize = async () => {
    if (!invoice.value) return
    showConfirm(
        'Finalize Invoice',
        'Are you sure? Once finalized, you cannot edit this invoice.',
        async () => {
            try {
                // We don't have updateStatus in store yet, let's use check finalizeInvoice or add updateStatus
                // The store has finalizeInvoice which calls /finalize.
                // But markAsSent usually implies just changing status if allowed.
                // Let's check store actions again.
                // Store has finalizeInvoice. It also has updateInvoice.
                // Let's use updateInvoice to set status to sent if that's what we want,
                // OR use finalizeInvoice if that's the intention.
                // The original code passed 'sent' to updateStatus which didn't exist in store props in lint,
                // but local `invoiceStore` definition might have had it?
                // Actually previous code was: await invoiceStore.updateStatus(invoice.value.id, 'sent')
                // But lint said it didn't exist.
                // Let's use updateInvoice.
                await invoiceStore.updateInvoice(invoice.value.id, { status: 'sent' })
                loadInvoice()
                showAlert('Invoice finalized successfully', 'Success', 'success')
            } catch (e: any) {
                showAlert(e.response?.data?.message || 'Failed to update status', 'Error', 'error')
            }
        },
        'Finalize'
    )
}

const getStatusClass = (status: string) => {
    switch (status) {
        case 'draft': return 'bg-gray-50 text-gray-600 ring-gray-500/10'
        case 'sent': return 'bg-blue-50 text-blue-700 ring-blue-700/10'
        case 'paid': return 'bg-green-50 text-green-700 ring-green-600/20'
        case 'overdue': return 'bg-red-50 text-red-700 ring-red-600/10'
        default: return 'bg-gray-50 text-gray-600 ring-gray-500/10'
    }
}

const duplicateInvoice = async () => {
    if (!invoice.value) return

    showConfirm(
        'Duplicate Invoice',
        'This will create a new draft invoice with the same items. Continue?',
        async () => {
            try {
                const newInvoice = await invoiceStore.duplicateInvoice(invoice.value.id)
                router.push(`/invoices/${newInvoice.id}/edit`)
            } catch (e: any) {
                showAlert(e.response?.data?.message || 'Failed to duplicate invoice', 'Error', 'error')
            }
        },
        'Duplicate'
    )
}

const createCreditNote = () => {
    if (!invoice.value) return
    router.push({ name: 'credit-note-create', query: { parent_id: invoice.value.id } })
}

const downloading = ref(false)
const downloadPdf = async () => {
    if (!invoice.value) return
    downloading.value = true

    try {
        // Use the content reference instead of querying by ID, as not all layouts use #invoice-paper
        if (!contentRef.value) throw new Error('Invoice content not found')

        // We want the 'inner' component root, usually the first child of the wrapper
        // The wrapper is contentRef. The component is inside.
        // Actually, contentRef IS the wrapper. Clone the wrapper's content (the component).
        // Let's just clone the wrapper's first child to get the Layout Component directly.
        const sourceElement = contentRef.value.firstElementChild as HTMLElement
        if (!sourceElement) throw new Error('Invoice layout element not found')

        // Create a clone
        const clone = sourceElement.cloneNode(true) as HTMLElement
        const stripModernEffects = (el: HTMLElement) => {
            el.classList?.remove('shadow-xl', 'shadow-lg', 'shadow-md', 'shadow-sm', 'shadow', 'ring-1', 'ring-2', 'ring-gray-900/5', 'ring-offset-2');
        }

        stripModernEffects(clone);
        const descendants = clone.getElementsByTagName('*');
        for (let i = 0; i < descendants.length; i++) {
            stripModernEffects(descendants[i] as HTMLElement);
        }

        // Reset styles for the clone to be "PDF ready"
        clone.style.margin = '0'
        clone.style.padding = '0' // REMOVE Padding (caused the extra white/gray space)
        clone.style.background = 'white' // Ensure white background
        clone.style.maxWidth = '210mm'
        clone.style.width = '210mm'
        // Allow auto height to support multi-page layouts (Default, Classic)
        clone.style.height = 'auto'
        clone.style.minHeight = '296mm' // At least one page
        clone.style.maxHeight = 'none'
        clone.style.overflow = 'visible' // Show all content
        clone.style.transform = 'none'

        // Wrap in a temporary container
        const container = document.createElement('div')
        container.style.position = 'fixed'
        container.style.left = '0'
        container.style.top = '0'
        container.style.zIndex = '-9999'
        container.style.width = '210mm'
        container.style.padding = '0'
        container.style.margin = '0'
        container.style.background = 'white'

        container.appendChild(clone)
        document.body.appendChild(container)

        // Ensure we are at the top to prevent scroll offset bugs
        window.scrollTo(0, 0)

        // Configure options
        const opt = {
            margin: 0, // Top, Left, Bottom, Right
            filename: `${invoice.value.invoice_number}.pdf`,
            image: { type: 'jpeg', quality: 0.98 } as const,
            html2canvas: {
                scale: 2,
                useCORS: true,
                logging: false,
                letterRendering: true,
                scrollY: 0,
                x: 0,
                y: 0,
                width: 794, // Force A4 width in px (approx 794px at 96dpi)
                windowWidth: 794 // Match window width to content width
            },
            jsPDF: { unit: 'mm', format: 'a4', orientation: 'portrait' as 'portrait' | 'landscape' }
        }

        if (typeof html2pdf !== 'function') {
            throw new Error('PDF library not loaded correctly. Please refresh.')
        }

        await html2pdf().set(opt).from(clone).save()

        document.body.removeChild(container)

    } catch (e: any) {
        console.error('Download failed', e)
        alert(`Failed to generate PDF: ${e.message || e}`)
    } finally {
        downloading.value = false
    }
}

// Share Logic
const shareMenuOpen = ref(false)

const getShareText = () => {
    if (!invoice.value) return ''
    const businessName = invoice.value.business?.name || 'Us'
    const invNum = invoice.value.invoice_number
    const amount = outstandingAmount.value
    const date = invoice.value.due_date ? `due on ${invoice.value.due_date}` : ''
    // Use Main URL (Landing Page) from env for public link
    const appUrl = import.meta.env.VITE_MAIN_URL || window.location.origin
    const publicLink = `${appUrl}/p/invoices/${invoice.value.id}`

    return `Hello ${invoice.value.party?.name || 'Customer'},\n\nThis is a gentle reminder from ${businessName}. Your Invoice ${invNum} for ₹${amount} is ${date}.\n\nYou can view and download your invoice here:\n${publicLink}\n\nPlease ensure payment is made at the earliest.\n\nThank you.`
}

const shareWhatsApp = () => {
    shareMenuOpen.value = false
    const phone = invoice.value?.party?.phone
    if (!phone) {
        showAlert('Customer phone number is missing.', 'Missing Info', 'warning')
        return
    }
    const text = encodeURIComponent(getShareText())
    window.open(`https://wa.me/${phone.replace(/[^0-9]/g, '')}?text=${text}`, '_blank')
}

const shareSMS = () => {
    shareMenuOpen.value = false
    const phone = invoice.value?.party?.phone
    if (!phone) {
        showAlert('Customer phone number is missing.', 'Missing Info', 'warning')
        return
    }
    const text = encodeURIComponent(getShareText())
    window.location.href = `sms:${phone.replace(/[^0-9]/g, '')}?body=${text}`
}

const sendingEmail = ref(false)
const sendEmail = async () => {
    if (!invoice.value) return
    if (!invoice.value.party?.email) {
        showAlert('This customer does not have an email address.', 'Missing Info', 'warning')
        return
    }

    showConfirm(
        'Send Email',
        `Send invoice PDF to ${invoice.value.party.email}?`,
        async () => {
            sendingEmail.value = true
            try {
                await client.post(`/invoices/${invoice.value.id}/email`)
                showAlert('Email sent successfully!', 'Success', 'success')
            } catch (e: any) {
                console.error('Email failed', e)
                showAlert(e.response?.data?.message || 'Failed to send email', 'Error', 'error')
            } finally {
                sendingEmail.value = false
            }
        },
        'Send'
    )
}

onMounted(() => {
    loadInvoice()
})
</script>

<style scoped>
/* GLOBAL PRINT RESET */
@media print {

    /* GLOBAL PRINT RESET */
    @page {
        margin: 0;
        size: A4;
    }

    :global(body > *:not(#print-container)) {
        display: none !important;
    }

    /* PRINT CONTAINER STYLES */
    :global(#print-container) {
        display: block !important;
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        margin: 0;
        padding: 0;
        background: white;
        z-index: 9999;
    }

    /* Ensure styles inside print container are standard */
    :global(#print-container *) {
        visibility: visible !important;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }

    /* Specific resets for the cloned nodes */
    :global(#print-container #invoice-paper),
    :global(#print-container .a4-page) {
        width: 210mm !important;
        max-width: 100% !important;
        margin: 0 auto !important;
        box-shadow: none !important;
        border: none !important;
        page-break-after: avoid !important;
        break-inside: avoid !important;
    }
}
</style>
