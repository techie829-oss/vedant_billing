@extends('layouts.web')

@section('content')
    <div class="bg-white py-16 px-4 overflow-hidden sm:px-6 lg:px-8 lg:py-24">
        <div class="relative max-w-xl mx-auto">
            <div class="text-center">
                <h2 class="text-3xl font-extrabold tracking-tight text-gray-900 sm:text-4xl">
                    Privacy Policy
                </h2>
                <p class="mt-4 text-lg leading-6 text-gray-500">
                    Last updated: {{ date('F d, Y') }}
                </p>
            </div>
            <div class="mt-12 prose prose-indigo prose-lg text-gray-500 mx-auto">
                <p>
                    Your privacy is important to us. It is Vedant Billing's policy to respect your privacy regarding any
                    information we may collect from you across our website, <a
                        href="{{ config('app.url') }}">{{ config('app.url') }}</a>, and other sites we own and operate.
                </p>
                <h3>1. Information We Collect</h3>
                <p>
                    We only ask for personal information when we truly need it to provide a service to you. We collect it by
                    fair and lawful means, with your knowledge and consent. We also let you know why we’re collecting it and
                    how it will be used.
                </p>
                <h3>2. How We Use Information</h3>
                <p>
                    We retain collected information for as long as necessary to provide you with your requested service.
                    What data we store, we’ll protect within commercially acceptable means to prevent loss and theft, as
                    well as unauthorized access, disclosure, copying, use or modification.
                </p>
                <h3>3. Sharing of Information</h3>
                <p>
                    We don’t share any personally identifying information publicly or with third-parties, except when
                    required to by law.
                </p>
                <h3>4. External Links</h3>
                <p>
                    Our website may link to external sites that are not operated by us. Please be aware that we have no
                    control over the content and practices of these sites, and cannot accept responsibility or liability for
                    their respective privacy policies.
                </p>
                <h3>5. Contact Us</h3>
                <p>
                    If you have any questions about how we handle user data and personal information, feel free to contact
                    us.
                </p>
            </div>
        </div>
    </div>
@endsection
