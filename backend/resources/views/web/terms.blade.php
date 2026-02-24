@extends('layouts.web')

@section('title', 'Terms of Service - VedantBilling')
@section('description',
    'Review VedantBilling\'s Terms and Conditions to understand the rules and guidelines for using
    our service.')
@section('keywords', 'terms and conditions, terms of service, user agreement, vedantbilling terms')

@section('content')
    <!-- Hero Section -->
    <div class="bg-blue-50 py-16 sm:py-24">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center">
                <h2 class="text-base font-semibold text-blue-600 tracking-wide uppercase">Legal</h2>
                <p class="mt-1 text-4xl font-extrabold text-gray-900 sm:text-5xl sm:tracking-tight lg:text-6xl">Terms and
                    Conditions</p>
                <p class="max-w-xl mx-auto mt-5 text-xl text-gray-500">Please read these terms carefully before using our
                    software and services.</p>
                <p class="mt-4 text-sm font-medium text-gray-400">Effective Date: February 24, 2026</p>
            </div>
        </div>
    </div>

    <!-- Content Section -->
    <div class="bg-gray-50 py-12">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white rounded-2xl shadow-xl border border-gray-100 overflow-hidden">
                <div class="p-8 sm:p-12 text-gray-600 leading-relaxed space-y-8 text-lg">

                    <!-- Introduction -->
                    <section>
                        <p>
                            By accessing the website at <a href="{{ config('app.url') }}"
                                class="text-blue-600 hover:text-blue-500">{{ config('app.url') }}</a>, you are
                            agreeing to be bound by these terms of service, all applicable laws and regulations, and agree
                            that you
                            are responsible for compliance with any applicable local laws.
                        </p>
                    </section>

                    <!-- Section 1 -->
                    <section>
                        <h2 class="text-2xl font-bold text-gray-900 mb-4 pb-2 border-b border-gray-200">1. Terms of Use
                            License</h2>
                        <p>
                            Permission is granted to temporarily download one copy of the materials (information or
                            software) on
                            Vedant Billing's website for personal, non-commercial transitory viewing only. This is the grant
                            of a license, not a transfer of title.
                        </p>
                    </section>

                    <!-- Section 2 -->
                    <section>
                        <h2 class="text-2xl font-bold text-gray-900 mb-4 pb-2 border-b border-gray-200">2. Disclaimer</h2>
                        <p>
                            The materials on Vedant Billing's website are provided on an 'as is' basis. Vedant Billing makes
                            no
                            warranties, expressed or implied, and hereby disclaims and negates all other warranties
                            including,
                            without limitation, implied warranties or conditions of merchantability, fitness for a
                            particular
                            purpose, or non-infringement of intellectual property or other violation of rights.
                        </p>
                    </section>

                    <!-- Section 3 -->
                    <section>
                        <h2 class="text-2xl font-bold text-gray-900 mb-4 pb-2 border-b border-gray-200">3. Limitations of
                            Liability</h2>
                        <p>
                            In no event shall Vedant Billing or its suppliers be liable for any damages (including, without
                            limitation, damages for loss of data or profit, or due to business interruption) arising out of
                            the use
                            or inability to use the materials on Vedant Billing's website, even if Vedant Billing or an
                            authorized representative has been notified orally or in writing of the possibility of such
                            damage.
                        </p>
                    </section>

                    <!-- Section 4 -->
                    <section>
                        <h2 class="text-2xl font-bold text-gray-900 mb-4 pb-2 border-b border-gray-200">4. Accuracy of
                            Materials</h2>
                        <p>
                            The materials appearing on Vedant Billing's website could include technical, typographical, or
                            photographic errors. Vedant Billing does not warrant that any of the materials on its website
                            are
                            accurate, complete or current. We may make changes to the materials contained on its website at
                            any time without notice.
                        </p>
                    </section>

                    <!-- Section 5 -->
                    <section>
                        <h2 class="text-2xl font-bold text-gray-900 mb-4 pb-2 border-b border-gray-200">5. Modifications to
                            Terms</h2>
                        <p>
                            Vedant Billing may revise these terms of service for its website at any time without notice. By
                            using
                            this website you are agreeing to be bound by the then current version of these terms of service.
                        </p>
                    </section>

                    <!-- Section 6 (Contact) -->
                    <section class="bg-blue-50 p-6 rounded-xl border border-blue-100 mt-10">
                        <h2 class="text-xl font-bold text-blue-900 mb-3">6. Questions or Concerns?</h2>
                        <p class="text-blue-800 mb-4">
                            If you have specific questions about these Terms and Conditions, please reach out to our support
                            team:
                        </p>
                        <div class="flex flex-col sm:flex-row sm:items-center space-y-3 sm:space-y-0 sm:space-x-6">
                            <a href="mailto:support@vedantbilling.com"
                                class="flex items-center text-blue-700 hover:text-blue-600 font-medium transition-colors">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z">
                                    </path>
                                </svg>
                                support@vedantbilling.com
                            </a>
                            <a href="https://wa.me/917007420572?text=Hello%20VedantBilling,%20I%20have%20an%20inquiry%20about%20your%20software."
                                target="_blank" rel="noopener noreferrer"
                                class="flex items-center text-green-600 hover:text-green-500 font-medium transition-colors">
                                <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path
                                        d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51a12.8 12.8 0 0 0-.57-.01c-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 0 1-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 0 1 2.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0 0 12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413Z" />
                                </svg>
                                WhatsApp Support
                            </a>
                        </div>
                    </section>

                </div>
            </div>
            <div class="mt-8 text-center sm:text-left">
                <a href="{{ route('home') }}"
                    class="inline-flex items-center text-sm font-medium text-blue-600 hover:text-blue-500">
                    <svg class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Return to Homepage
                </a>
            </div>
        </div>
    </div>
@endsection
