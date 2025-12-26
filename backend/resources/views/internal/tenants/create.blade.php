@extends('internal.layouts.app')

@section('header')
    <div class="flex items-center">
        <a href="{{ route('internal.tenants.index') }}" class="mr-4 text-text-muted hover:text-text">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18">
                </path>
            </svg>
        </a>
        Add New Tenant
    </div>
@endsection

@section('content')
    <div class="max-w-2xl mx-auto">
        <div class="bg-surface shadow-sm border border-gray-200 rounded-lg p-6">
            <form method="POST" action="{{ route('internal.tenants.store') }}">
                @csrf

                <div class="grid grid-cols-1 gap-6">
                    <!-- Business Name -->
                    <div>
                        <label for="name" class="block text-sm font-medium text-text-muted mb-1">Business Name</label>
                        <input type="text" name="name" id="name" class="input-field" required value="{{ old('name') }}"
                            placeholder="e.g. Acme Corp">
                        @error('name') <p class="text-sm text-danger mt-1">{{ $message }}</p> @enderror
                    </div>

                    <!-- Admin Email -->
                    <div>
                        <label for="email" class="block text-sm font-medium text-text-muted mb-1">Owner Email</label>
                        <input type="email" name="email" id="email" class="input-field" required value="{{ old('email') }}"
                            placeholder="admin@acme.com">
                        <p class="text-xs text-text-muted mt-1">An initial admin user will be created with this email.</p>
                        @error('email') <p class="text-sm text-danger mt-1">{{ $message }}</p> @enderror
                    </div>

                    <!-- Additional Details -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Mobile -->
                        <div>
                            <label for="mobile" class="block text-sm font-medium text-text-muted mb-1">Mobile No.</label>
                            <input type="text" name="mobile" id="mobile" class="input-field" value="{{ old('mobile') }}"
                                placeholder="e.g. +91 9876543210">
                            @error('mobile') <p class="text-sm text-danger mt-1">{{ $message }}</p> @enderror
                        </div>

                        <!-- Website -->
                        <div>
                            <label for="website" class="block text-sm font-medium text-text-muted mb-1">Website (Optional)</label>
                            <input type="url" name="website" id="website" class="input-field" value="{{ old('website') }}"
                                placeholder="https://">
                            @error('website') <p class="text-sm text-danger mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- GSTIN -->
                        <div>
                            <label for="gstin" class="block text-sm font-medium text-text-muted mb-1">GSTIN (Optional)</label>
                            <input type="text" name="gstin" id="gstin" class="input-field uppercase" value="{{ old('gstin') }}"
                                placeholder="22AAAAA0000A1Z5">
                            @error('gstin') <p class="text-sm text-danger mt-1">{{ $message }}</p> @enderror
                        </div>

                        <!-- PAN -->
                        <div>
                            <label for="pan" class="block text-sm font-medium text-text-muted mb-1">PAN (Optional)</label>
                            <input type="text" name="pan" id="pan" class="input-field uppercase" value="{{ old('pan') }}"
                                placeholder="ABCDE1234F">
                            @error('pan') <p class="text-sm text-danger mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <!-- Bank Details -->
                    <div class="border-t border-gray-100 pt-4 mt-2">
                        <h4 class="text-sm font-semibold text-text mb-3">Bank Details (Optional)</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="bank_name" class="block text-sm font-medium text-text-muted mb-1">Bank Name</label>
                                <input type="text" name="bank_name" id="bank_name" class="input-field" value="{{ old('bank_name') }}" placeholder="HDFC Bank">
                            </div>
                            <div>
                                <label for="ifsc_code" class="block text-sm font-medium text-text-muted mb-1">IFSC Code</label>
                                <input type="text" name="ifsc_code" id="ifsc_code" class="input-field uppercase" value="{{ old('ifsc_code') }}" placeholder="HDFC0001234">
                            </div>
                            <div class="md:col-span-2">
                                <label for="account_number" class="block text-sm font-medium text-text-muted mb-1">Account Number</label>
                                <input type="text" name="account_number" id="account_number" class="input-field" value="{{ old('account_number') }}" placeholder="50100...">
                            </div>
                        </div>
                    </div>

                    <!-- Address -->
                    <div>
                        <label for="address" class="block text-sm font-medium text-text-muted mb-1">Billing Address</label>
                        <textarea name="address" id="address" rows="3" class="input-field" placeholder="Full business address...">{{ old('address') }}</textarea>
                        @error('address') <p class="text-sm text-danger mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="bg-blue-50 border border-blue-100 rounded-md p-4 text-sm text-blue-800">
                        <strong>Note:</strong> A default password <code>password</code> will be assigned. The user can
                        change it later.
                    </div>

                    <div class="pt-4 border-t border-gray-100 flex justify-end">
                        <button type="submit" class="btn-primary">Create Tenant</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection