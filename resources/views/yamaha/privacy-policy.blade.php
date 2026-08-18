@extends('yamaha.layout')

@section('title', 'Privacy Policy')
@section('meta_description', 'Star Yamaha privacy policy — how we collect, use and protect your personal information.')
@section('canonical', url('/privacy-policy'))

@section('content')

    {{-- Page header --}}
    <div class="bg-ink border-b-2 border-brand">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
            <nav class="text-gray-500 text-xs uppercase tracking-widest font-semibold mb-4">
                <a href="{{ route('yamaha.index') }}" class="hover:text-white transition">Home</a>
                <span class="mx-2">›</span>
                <span class="text-white">Privacy Policy</span>
            </nav>
            <h1 class="text-4xl font-black uppercase text-white tracking-tight">Privacy Policy</h1>
        </div>
    </div>

    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-14">
        <div class="prose prose-gray max-w-none text-gray-600 leading-relaxed">

            <p>
                Star Yamaha is committed to providing you with the best possible customer experience.
                Star Yamaha is bound by the Privacy Act 1988 (Cth), which sets out the principles concerning the privacy of individuals.
            </p>

            <h2>Collection of Your Personal Information</h2>
            <p>
                There are many aspects of the site which can be viewed without providing personal information, however, for access to future
                customer support features you are required to submit personally identifiable information. This may include, but is not limited to:
            </p>
            <ul>
                <li>Unique username and password</li>
                <li>Personal information to recover a lost password</li>
                <li>Name and address</li>
                <li>Contact information including email address</li>
                <li>Demographic information such as postcode, preferences and interests</li>
                <li>Information relevant to customer surveys and/or offers</li>
            </ul>

            <h2>Sharing of Your Personal Information</h2>
            <p>
                We will never sell your information on to third parties, however, we may occasionally hire other companies to provide services
                on our behalf, including but not limited to handling customer support enquiries, processing transactions or customer freight
                shipping. Those companies will be permitted to obtain only the personal information they need to deliver the service.
                We take reasonable steps to ensure that these organisations are bound by confidentiality and privacy obligations in relation
                to the protection of your personal information.
            </p>

            <h2>Use of Your Personal Information and Cookies</h2>
            <p>
                A cookie is a small file which asks permission to be placed on your computer's hard drive. Once you agree, the file is added,
                and the cookie helps analyse web traffic or lets you know when you visit a particular site. Cookies allow web applications
                to respond to you as an individual. The web application can tailor its operations to your needs, likes and dislikes by
                gathering and remembering information about your preferences.
            </p>
            <p>
                We expressly collect the following non-personally identifiable information, including but not limited to browser type,
                version and language, operating system, pages viewed while browsing the site, page access times and referring website
                address. This collected information is used solely internally for the purpose of gauging visitor traffic, trends and
                delivering personalised content to you while you are at this site.
            </p>
            <p>
                Overall, cookies help us provide you with a better website by enabling us to monitor which pages you find useful and
                which you do not. A cookie in no way gives us access to your computer or any information about you, other than the data
                you choose to share with us.
            </p>
            <p>
                You can choose to accept or decline cookies. Most web browsers automatically accept cookies, but you can usually modify
                your browser setting to decline cookies if you prefer. This may prevent you from taking full advantage of the website.
            </p>
            <p>
                From time to time, we may use customer information for new, unanticipated uses not previously disclosed in our privacy
                notice. If our information practices change at some time in the future we will use for these new purposes only data
                collected from the time of the policy change forward, and will adhere to our updated practices.
            </p>

            <h2>Links to Other Websites</h2>
            <p>
                Our website may contain links to other websites of interest. However, once you have used these links to leave our site,
                you should note that we do not have any control over that other website. Therefore, we cannot be responsible for the
                protection and privacy of any information which you provide whilst visiting such sites, and such sites are not governed
                by this privacy statement. You should exercise caution and look at the privacy statement applicable to the website
                in question.
            </p>

            <h2>Changes to This Privacy Policy</h2>
            <p>
                Star Yamaha reserves the right to make amendments to this Privacy Policy at any time.
                If you have objections to the Privacy Policy, you should not access or use the site.
            </p>

            <h2>Contacting Us</h2>
            <p>Star Yamaha welcomes your comments regarding this Privacy Policy. If you have any questions and would like further information, please contact us by any of the following means:</p>
            <p>
                <strong>Business Hours</strong><br>
                Call: <a href="{{ config('dealership.phone.href') }}">{{ config('dealership.phone.display') }}</a>
            </p>
            <p>
                <strong>Anytime</strong><br>
                Email: <a href="mailto:{{ config('dealership.email.sales') }}">{{ config('dealership.email.sales') }}</a>
            </p>

        </div>
    </div>

@endsection
