@extends('layouts.app')
@section('title', 'Privacy Policy')
@section('content')
<div class="max-w-4xl mx-auto px-4 py-8">
    <div class="bg-white rounded-lg shadow-lg p-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-6">Privacy Policy</h1>
        <p class="text-sm text-gray-500 mb-8">Last updated: {{ date('F d, Y') }}</p>
        
        <div class="prose max-w-none space-y-6 text-gray-700">
            <section>
                <h2 class="text-2xl font-semibold text-gray-900 mb-4">1. Information We Collect</h2>
                <p>We collect information that you provide directly to us, including:</p>
                <ul class="list-disc pl-6 mt-2 space-y-1">
                    <li>Name and email address when you register for an account</li>
                    <li>Profile information, portfolio content, and other data you choose to provide</li>
                    <li>Information about your use of the Service, including IP address, browser type, and access times</li>
                </ul>
            </section>

            <section>
                <h2 class="text-2xl font-semibold text-gray-900 mb-4">2. How We Use Your Information</h2>
                <p>We use the information we collect to:</p>
                <ul class="list-disc pl-6 mt-2 space-y-1">
                    <li>Provide, maintain, and improve our Service</li>
                    <li>Process your registration and manage your account</li>
                    <li>Send you technical notices and support messages</li>
                    <li>Respond to your comments, questions, and requests</li>
                    <li>Monitor and analyze trends, usage, and activities in connection with our Service</li>
                </ul>
            </section>

            <section>
                <h2 class="text-2xl font-semibold text-gray-900 mb-4">3. Information Sharing and Disclosure</h2>
                <p>We do not sell, trade, or rent your personal information to third parties. We may share your information only in the following circumstances:</p>
                <ul class="list-disc pl-6 mt-2 space-y-1">
                    <li>With your consent or at your direction</li>
                    <li>To comply with legal obligations or respond to lawful requests</li>
                    <li>To protect our rights, privacy, safety, or property</li>
                    <li>In connection with a business transfer (merger, acquisition, etc.)</li>
                </ul>
            </section>

            <section>
                <h2 class="text-2xl font-semibold text-gray-900 mb-4">4. Data Security</h2>
                <p>We implement appropriate technical and organizational measures to protect your personal information. However, no method of transmission over the Internet or electronic storage is 100% secure, and we cannot guarantee absolute security.</p>
            </section>

            <section>
                <h2 class="text-2xl font-semibold text-gray-900 mb-4">5. Your Rights</h2>
                <p>You have the right to:</p>
                <ul class="list-disc pl-6 mt-2 space-y-1">
                    <li>Access and receive a copy of your personal data</li>
                    <li>Rectify inaccurate or incomplete personal data</li>
                    <li>Request deletion of your personal data</li>
                    <li>Object to processing of your personal data</li>
                    <li>Request restriction of processing your personal data</li>
                    <li>Data portability</li>
                </ul>
            </section>

            <section>
                <h2 class="text-2xl font-semibold text-gray-900 mb-4">6. Cookies and Tracking Technologies</h2>
                <p>We use cookies and similar tracking technologies to track activity on our Service and hold certain information. You can instruct your browser to refuse all cookies or to indicate when a cookie is being sent.</p>
            </section>

            <section>
                <h2 class="text-2xl font-semibold text-gray-900 mb-4">7. Data Retention</h2>
                <p>We retain your personal information for as long as your account is active or as needed to provide you services. We will also retain and use your information as necessary to comply with our legal obligations, resolve disputes, and enforce our agreements.</p>
            </section>

            <section>
                <h2 class="text-2xl font-semibold text-gray-900 mb-4">8. Children's Privacy</h2>
                <p>Our Service is not intended for children under the age of 13. We do not knowingly collect personal information from children under 13.</p>
            </section>

            <section>
                <h2 class="text-2xl font-semibold text-gray-900 mb-4">9. Changes to This Privacy Policy</h2>
                <p>We may update our Privacy Policy from time to time. We will notify you of any changes by posting the new Privacy Policy on this page and updating the "Last updated" date.</p>
            </section>

            <section>
                <h2 class="text-2xl font-semibold text-gray-900 mb-4">10. Contact Us</h2>
                <p>If you have any questions about this Privacy Policy, please contact us.</p>
            </section>
        </div>

        <div class="mt-8 pt-6 border-t border-gray-200">
            <a href="{{ route('register') }}" class="text-blue-600 hover:text-blue-800">← Back to Registration</a>
        </div>
    </div>
</div>
@endsection

