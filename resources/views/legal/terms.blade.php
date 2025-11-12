@extends('layouts.app')
@section('title', 'Terms and Conditions')
@section('content')
<div class="max-w-4xl mx-auto px-4 py-8">
    <div class="bg-white rounded-lg shadow-lg p-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-6">Terms and Conditions</h1>
        <p class="text-sm text-gray-500 mb-8">Last updated: {{ date('F d, Y') }}</p>
        
        <div class="prose max-w-none space-y-6 text-gray-700">
            <section>
                <h2 class="text-2xl font-semibold text-gray-900 mb-4">1. Acceptance of Terms</h2>
                <p>By accessing and using MyFolioPage ("the Service"), you accept and agree to be bound by the terms and provision of this agreement. If you do not agree to abide by the above, please do not use this service.</p>
            </section>

            <section>
                <h2 class="text-2xl font-semibold text-gray-900 mb-4">2. Use License</h2>
                <p>Permission is granted to temporarily use MyFolioPage for personal, non-commercial transitory viewing only. This is the grant of a license, not a transfer of title, and under this license you may not:</p>
                <ul class="list-disc pl-6 mt-2 space-y-1">
                    <li>modify or copy the materials;</li>
                    <li>use the materials for any commercial purpose, or for any public display (commercial or non-commercial);</li>
                    <li>attempt to decompile or reverse engineer any software contained on MyFolioPage;</li>
                    <li>remove any copyright or other proprietary notations from the materials; or</li>
                    <li>transfer the materials to another person or "mirror" the materials on any other server.</li>
                </ul>
            </section>

            <section>
                <h2 class="text-2xl font-semibold text-gray-900 mb-4">3. User Accounts</h2>
                <p>You are responsible for maintaining the confidentiality of your account and password. You agree to accept responsibility for all activities that occur under your account or password.</p>
            </section>

            <section>
                <h2 class="text-2xl font-semibold text-gray-900 mb-4">4. User Content</h2>
                <p>You retain ownership of any content you submit, post or display on or through the Service. By submitting, posting or displaying content on or through the Service, you grant us a worldwide, non-exclusive, royalty-free license to use, reproduce, modify, adapt, publish, translate, and distribute such content for the purpose of operating and providing the Service.</p>
            </section>

            <section>
                <h2 class="text-2xl font-semibold text-gray-900 mb-4">5. Prohibited Uses</h2>
                <p>You may not use the Service:</p>
                <ul class="list-disc pl-6 mt-2 space-y-1">
                    <li>In any way that violates any applicable national or international law or regulation</li>
                    <li>To transmit, or procure the sending of, any advertising or promotional material without our prior written consent</li>
                    <li>To impersonate or attempt to impersonate the company, a company employee, another user, or any other person or entity</li>
                    <li>In any way that infringes upon the rights of others, or in any way is illegal, threatening, fraudulent, or harmful</li>
                </ul>
            </section>

            <section>
                <h2 class="text-2xl font-semibold text-gray-900 mb-4">6. Disclaimer</h2>
                <p>The materials on MyFolioPage are provided on an 'as is' basis. MyFolioPage makes no warranties, expressed or implied, and hereby disclaims and negates all other warranties including without limitation, implied warranties or conditions of merchantability, fitness for a particular purpose, or non-infringement of intellectual property or other violation of rights.</p>
            </section>

            <section>
                <h2 class="text-2xl font-semibold text-gray-900 mb-4">7. Limitations</h2>
                <p>In no event shall MyFolioPage or its suppliers be liable for any damages (including, without limitation, damages for loss of data or profit, or due to business interruption) arising out of the use or inability to use the materials on MyFolioPage, even if MyFolioPage or a MyFolioPage authorized representative has been notified orally or in writing of the possibility of such damage.</p>
            </section>

            <section>
                <h2 class="text-2xl font-semibold text-gray-900 mb-4">8. Account Termination</h2>
                <p>We reserve the right to terminate or suspend your account and access to the Service immediately, without prior notice or liability, for any reason whatsoever, including without limitation if you breach the Terms.</p>
            </section>

            <section>
                <h2 class="text-2xl font-semibold text-gray-900 mb-4">9. Changes to Terms</h2>
                <p>We reserve the right, at our sole discretion, to modify or replace these Terms at any time. If a revision is material, we will provide at least 30 days notice prior to any new terms taking effect.</p>
            </section>

            <section>
                <h2 class="text-2xl font-semibold text-gray-900 mb-4">10. Contact Information</h2>
                <p>If you have any questions about these Terms, please contact us.</p>
            </section>
        </div>

        <div class="mt-8 pt-6 border-t border-gray-200">
            <a href="{{ route('register') }}" class="text-blue-600 hover:text-blue-800">← Back to Registration</a>
        </div>
    </div>
</div>
@endsection





