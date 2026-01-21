@extends('layouts.web')

@section('title', 'Terms of Service - VedantBilling')
@section('description',
    'Review VedantBilling\'s Terms and Conditions to understand the rules and guidelines for using
    our service.')
@section('keywords', 'terms and conditions, terms of service, user agreement, vedantbilling terms')

@section('content')
    <div class="bg-white py-16 px-4 overflow-hidden sm:px-6 lg:px-8 lg:py-24">
        <div class="relative max-w-xl mx-auto">
            <div class="text-center">
                <h2 class="text-3xl font-extrabold tracking-tight text-gray-900 sm:text-4xl">
                    Terms and Conditions
                </h2>
                <p class="mt-4 text-lg leading-6 text-gray-500">
                    Last updated: {{ date('F d, Y') }}
                </p>
            </div>
            <div class="mt-12 prose prose-indigo prose-lg text-gray-500 mx-auto">
                <h3>1. Terms</h3>
                <p>
                    By accessing the website at <a href="{{ config('app.url') }}">{{ config('app.url') }}</a>, you are
                    agreeing to be bound by these terms of service, all applicable laws and regulations, and agree that you
                    are responsible for compliance with any applicable local laws.
                </p>
                <h3>2. Use License</h3>
                <p>
                    Permission is granted to temporarily download one copy of the materials (information or software) on
                    Vedant Billing's website for personal, non-commercial transitory viewing only.
                </p>
                <h3>3. Disclaimer</h3>
                <p>
                    The materials on Vedant Billing's website are provided on an 'as is' basis. Vedant Billing makes no
                    warranties, expressed or implied, and hereby disclaims and negates all other warranties including,
                    without limitation, implied warranties or conditions of merchantability, fitness for a particular
                    purpose, or non-infringement of intellectual property or other violation of rights.
                </p>
                <h3>4. Limitations</h3>
                <p>
                    In no event shall Vedant Billing or its suppliers be liable for any damages (including, without
                    limitation, damages for loss of data or profit, or due to business interruption) arising out of the use
                    or inability to use the materials on Vedant Billing's website.
                </p>
                <h3>5. Accuracy of Materials</h3>
                <p>
                    The materials appearing on Vedant Billing's website could include technical, typographical, or
                    photographic errors. Vedant Billing does not warrant that any of the materials on its website are
                    accurate, complete or current.
                </p>
                <h3>6. Modifications</h3>
                <p>
                    Vedant Billing may revise these terms of service for its website at any time without notice. By using
                    this website you are agreeing to be bound by the then current version of these terms of service.
                </p>
            </div>
        </div>
    </div>
@endsection
