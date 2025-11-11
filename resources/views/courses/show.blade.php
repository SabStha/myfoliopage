@extends('layouts.app')
@section('title', $course->getTranslated('title') . ' - Course Details')
@section('content')

<div class="min-h-screen bg-neutral-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-5xl mx-auto">
        <!-- Header Section -->
        <div class="bg-white rounded-2xl shadow-lg overflow-hidden mb-8">
            <div class="bg-gradient-to-r from-neutral-900 via-neutral-800 to-neutral-900 px-8 py-12 text-white">
                <div class="flex flex-col md:flex-row items-start md:items-center gap-6">
                    <!-- Course Image -->
                    @php
                        $courseImage = $course->media->first();
                        $imageUrl = $courseImage ? ($courseImage->path ?? '/storage/courses/default.jpg') : '/storage/courses/default.jpg';
                    @endphp
                    <div class="flex-shrink-0">
                        <img src="{{ $imageUrl }}" alt="{{ $course->getTranslated('title') }}" 
                             class="w-48 h-32 object-cover rounded-xl shadow-xl">
                    </div>
                    
                    <!-- Course Info -->
                    <div class="flex-1">
                        <h1 class="text-3xl md:text-4xl font-black mb-3 leading-tight">
                            {{ $course->getTranslated('title') }}
                        </h1>
                        @if($course->getTranslated('provider'))
                        <p class="text-neutral-300 text-lg mb-2">
                            {{ $course->getTranslated('provider') }}
                        </p>
                        @endif
                        @if($course->credential_id)
                        <p class="text-neutral-400 text-sm">
                            Credential ID: <span class="font-semibold">{{ $course->credential_id }}</span>
                        </p>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Overview Section -->
        <section class="bg-white rounded-2xl shadow-md p-8 mb-8">
            <h2 class="text-2xl font-bold text-neutral-900 mb-6 flex items-center gap-3">
                <span class="w-1 h-8 bg-[#ffb400] rounded"></span>
                Overview
            </h2>
            <p class="text-neutral-700 leading-relaxed text-lg">
                {{ $course->description ?? 'This course is designed to prepare learners for the AWS Certified Cloud Practitioner (CLF-C02) exam. It covers foundational cloud concepts, AWS core services, security and compliance, pricing and billing, and basic architectural practices.' }}
            </p>
        </section>

        <!-- What You'll Learn Section -->
        <section class="bg-white rounded-2xl shadow-md p-8 mb-8">
            <h2 class="text-2xl font-bold text-neutral-900 mb-6 flex items-center gap-3">
                <span class="w-1 h-8 bg-[#ffb400] rounded"></span>
                What You'll Learn
            </h2>
            <div class="grid md:grid-cols-2 gap-4">
                <div class="flex items-start gap-3 p-4 bg-neutral-50 rounded-lg hover:bg-neutral-100 transition-colors">
                    <svg class="w-6 h-6 text-[#ffb400] flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    <p class="text-neutral-700">Fundamental cloud computing & AWS concepts: what cloud is, global infrastructure, benefits.</p>
                </div>
                <div class="flex items-start gap-3 p-4 bg-neutral-50 rounded-lg hover:bg-neutral-100 transition-colors">
                    <svg class="w-6 h-6 text-[#ffb400] flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    <p class="text-neutral-700">Core AWS services: compute (EC2, Lambda), storage (S3, EBS), networking (VPC, Route 53), databases.</p>
                </div>
                <div class="flex items-start gap-3 p-4 bg-neutral-50 rounded-lg hover:bg-neutral-100 transition-colors">
                    <svg class="w-6 h-6 text-[#ffb400] flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    <p class="text-neutral-700">Security, compliance, and shared-responsibility model.</p>
                </div>
                <div class="flex items-start gap-3 p-4 bg-neutral-50 rounded-lg hover:bg-neutral-100 transition-colors">
                    <svg class="w-6 h-6 text-[#ffb400] flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    <p class="text-neutral-700">AWS pricing models, cost optimisation, billing fundamentals.</p>
                </div>
                <div class="flex items-start gap-3 p-4 bg-neutral-50 rounded-lg hover:bg-neutral-100 transition-colors">
                    <svg class="w-6 h-6 text-[#ffb400] flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    <p class="text-neutral-700">How to relate AWS services to real-world business scenarios and cloud adoption frameworks.</p>
                </div>
                <div class="flex items-start gap-3 p-4 bg-neutral-50 rounded-lg hover:bg-neutral-100 transition-colors">
                    <svg class="w-6 h-6 text-[#ffb400] flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    <p class="text-neutral-700">Practice exams and labs (depending on edition) to reinforce learning and exam readiness.</p>
                </div>
            </div>
        </section>

        <!-- Skills Section -->
        <section class="bg-white rounded-2xl shadow-md p-8 mb-8">
            <h2 class="text-2xl font-bold text-neutral-900 mb-6 flex items-center gap-3">
                <span class="w-1 h-8 bg-[#ffb400] rounded"></span>
                Skills You'll Gain
            </h2>
            <div class="space-y-4">
                <div class="flex items-start gap-4 p-5 bg-gradient-to-r from-neutral-50 to-transparent rounded-xl border-l-4 border-[#ffb400]">
                    <div class="flex-shrink-0 w-10 h-10 rounded-full bg-[#ffb400]/10 flex items-center justify-center">
                        <svg class="w-5 h-5 text-[#ffb400]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
                        </svg>
                    </div>
                    <div>
                        <p class="font-semibold text-neutral-900 mb-1">Cloud Fundamentals</p>
                        <p class="text-neutral-600">Ability to explain what the cloud is, how AWS operates, and why organisations use cloud services.</p>
                    </div>
                </div>
                <div class="flex items-start gap-4 p-5 bg-gradient-to-r from-neutral-50 to-transparent rounded-xl border-l-4 border-[#ffb400]">
                    <div class="flex-shrink-0 w-10 h-10 rounded-full bg-[#ffb400]/10 flex items-center justify-center">
                        <svg class="w-5 h-5 text-[#ffb400]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                        </svg>
                    </div>
                    <div>
                        <p class="font-semibold text-neutral-900 mb-1">AWS Services Mastery</p>
                        <p class="text-neutral-600">Familiarity with AWS's major services and how they interconnect.</p>
                    </div>
                </div>
                <div class="flex items-start gap-4 p-5 bg-gradient-to-r from-neutral-50 to-transparent rounded-xl border-l-4 border-[#ffb400]">
                    <div class="flex-shrink-0 w-10 h-10 rounded-full bg-[#ffb400]/10 flex items-center justify-center">
                        <svg class="w-5 h-5 text-[#ffb400]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                        </svg>
                    </div>
                    <div>
                        <p class="font-semibold text-neutral-900 mb-1">Security & Governance</p>
                        <p class="text-neutral-600">Understanding of how AWS security and governance models function.</p>
                    </div>
                </div>
                <div class="flex items-start gap-4 p-5 bg-gradient-to-r from-neutral-50 to-transparent rounded-xl border-l-4 border-[#ffb400]">
                    <div class="flex-shrink-0 w-10 h-10 rounded-full bg-[#ffb400]/10 flex items-center justify-center">
                        <svg class="w-5 h-5 text-[#ffb400]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div>
                        <p class="font-semibold text-neutral-900 mb-1">Cost Management</p>
                        <p class="text-neutral-600">Insight into cost management and billing in AWS environments.</p>
                    </div>
                </div>
                <div class="flex items-start gap-4 p-5 bg-gradient-to-r from-neutral-50 to-transparent rounded-xl border-l-4 border-[#ffb400]">
                    <div class="flex-shrink-0 w-10 h-10 rounded-full bg-[#ffb400]/10 flex items-center justify-center">
                        <svg class="w-5 h-5 text-[#ffb400]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"></path>
                        </svg>
                    </div>
                    <div>
                        <p class="font-semibold text-neutral-900 mb-1">Exam Preparedness</p>
                        <p class="text-neutral-600">Preparedness for the AWS Certified Cloud Practitioner exam.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Who This Course is For Section -->
        <section class="bg-white rounded-2xl shadow-md p-8 mb-8">
            <h2 class="text-2xl font-bold text-neutral-900 mb-6 flex items-center gap-3">
                <span class="w-1 h-8 bg-[#ffb400] rounded"></span>
                Who This Course is For
            </h2>
            <div class="grid md:grid-cols-2 gap-6">
                <div class="p-6 bg-neutral-50 rounded-xl hover:shadow-md transition-shadow">
                    <div class="flex items-center gap-3 mb-3">
                        <div class="w-12 h-12 rounded-full bg-[#ffb400] flex items-center justify-center">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                            </svg>
                        </div>
                        <h3 class="font-bold text-neutral-900 text-lg">Beginners</h3>
                    </div>
                    <p class="text-neutral-600">Beginners with little or no AWS experience who want to gain a foundational credential.</p>
                </div>
                <div class="p-6 bg-neutral-50 rounded-xl hover:shadow-md transition-shadow">
                    <div class="flex items-center gap-3 mb-3">
                        <div class="w-12 h-12 rounded-full bg-[#ffb400] flex items-center justify-center">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                            </svg>
                        </div>
                        <h3 class="font-bold text-neutral-900 text-lg">IT Professionals</h3>
                    </div>
                    <p class="text-neutral-600">IT professionals transitioning toward cloud roles.</p>
                </div>
                <div class="p-6 bg-neutral-50 rounded-xl hover:shadow-md transition-shadow">
                    <div class="flex items-center gap-3 mb-3">
                        <div class="w-12 h-12 rounded-full bg-[#ffb400] flex items-center justify-center">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                            </svg>
                        </div>
                        <h3 class="font-bold text-neutral-900 text-lg">Business Professionals</h3>
                    </div>
                    <p class="text-neutral-600">Business professionals who want to understand cloud computing and AWS from a managerial or oversight vantage.</p>
                </div>
                <div class="p-6 bg-neutral-50 rounded-xl hover:shadow-md transition-shadow">
                    <div class="flex items-center gap-3 mb-3">
                        <div class="w-12 h-12 rounded-full bg-[#ffb400] flex items-center justify-center">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                            </svg>
                        </div>
                        <h3 class="font-bold text-neutral-900 text-lg">Certification Seekers</h3>
                    </div>
                    <p class="text-neutral-600">Anyone wanting a structured overview of AWS before diving into deeper certifications.</p>
                </div>
            </div>
        </section>

        <!-- Summary Section -->
        <section class="bg-gradient-to-br from-neutral-900 to-neutral-800 rounded-2xl shadow-xl p-8 text-white">
            <div class="flex items-start gap-4">
                <div class="flex-shrink-0 w-12 h-12 rounded-full bg-[#ffb400] flex items-center justify-center">
                    <svg class="w-6 h-6 text-neutral-900" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div>
                    <h2 class="text-2xl font-bold mb-4">Summary</h2>
                    <p class="text-neutral-200 leading-relaxed text-lg">
                        In short: this Udemy course is a solid entry point into AWS. It balances theory, core services, business-relevance, and exam preparation. While it may not replace deeper hands-on practises, it provides the foundation and confidence to pass the Cloud Practitioner exam and move into more advanced AWS roles.
                    </p>
                </div>
            </div>
        </section>

        <!-- Action Buttons -->
        <div class="mt-8 flex flex-col sm:flex-row gap-4 justify-center">
            @if($course->verify_url)
            <a href="{{ $course->verify_url }}" target="_blank" 
               class="inline-flex items-center justify-center px-8 py-4 bg-[#ffb400] text-neutral-900 font-bold rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 hover:scale-105">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                </svg>
                Verify Certificate
            </a>
            @endif
            <a href="{{ route('home') }}#my-works" 
               class="inline-flex items-center justify-center px-8 py-4 bg-white text-neutral-900 font-bold rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 hover:scale-105 border-2 border-neutral-200">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Back to Portfolio
            </a>
        </div>
    </div>
</div>

@endsection




