<x-guest-layout>
    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <div class="mb-8 text-center">
        <h2 class="text-3xl font-bold text-gray-900 mb-2">{{ __('auth.welcome_back') }}</h2>
        <p class="text-gray-600">{{ __('auth.login_subtitle') }}</p>
    </div>

    <form method="POST" action="{{ route('login') }}" class="space-y-6">
        @csrf

        <!-- Email Address -->
        <div>
            <label for="email" class="block text-sm font-semibold text-gray-700 mb-2">
                {{ __('auth.email') }}
            </label>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"></path>
                    </svg>
                </div>
                <input 
                    id="email" 
                    type="email" 
                    name="email" 
                    value="{{ old('email') }}" 
                    required 
                    autofocus 
                    autocomplete="username"
                    placeholder="{{ __('auth.email_placeholder') }}"
                    class="block w-full pl-10 pr-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-[#ffb400] focus:border-[#ffb400] transition-all duration-200 text-gray-900 placeholder-gray-400"
                />
            </div>
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div>
            <label for="password" class="block text-sm font-semibold text-gray-700 mb-2">
                {{ __('auth.password') }}
            </label>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                    </svg>
                </div>
                <input 
                    id="password" 
                    type="password" 
                    name="password" 
                    required 
                    autocomplete="current-password"
                    placeholder="{{ __('auth.password_placeholder') }}"
                    class="block w-full pl-10 pr-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-[#ffb400] focus:border-[#ffb400] transition-all duration-200 text-gray-900 placeholder-gray-400"
                />
            </div>
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Remember Me & Forgot Password -->
        <div class="flex items-center justify-between">
            <label for="remember_me" class="inline-flex items-center cursor-pointer">
                <input 
                    id="remember_me" 
                    type="checkbox" 
                    name="remember" 
                    class="w-4 h-4 text-[#ffb400] bg-gray-100 border-gray-300 rounded focus:ring-[#ffb400] focus:ring-2"
                />
                <span class="ml-2 text-sm text-gray-600">{{ __('auth.remember_me') }}</span>
            </label>

            @if (Route::has('password.request'))
                <a 
                    href="{{ route('password.request') }}" 
                    class="text-sm font-medium text-[#ffb400] hover:text-[#ff9500] transition-colors duration-200"
                >
                    {{ __('auth.forgot_password') }}
                </a>
            @endif
        </div>

        <!-- Submit Button -->
        <button 
            type="submit" 
            class="w-full bg-gradient-to-r from-[#ffb400] to-[#ff9500] text-white font-semibold py-3 px-4 rounded-xl shadow-lg hover:shadow-xl transform hover:scale-[1.02] transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-[#ffb400] focus:ring-offset-2"
        >
            {{ __('auth.login') }}
        </button>
    </form>

    <!-- Divider -->
    <div class="mt-8 flex items-center">
        <div class="flex-1 border-t border-gray-300"></div>
        <span class="px-4 text-sm text-gray-500">{{ __('auth.or') }}</span>
        <div class="flex-1 border-t border-gray-300"></div>
    </div>

    <!-- Register Link -->
    <div class="mt-6 text-center">
        <p class="text-sm text-gray-600">
            {{ __('auth.dont_have_account') }}
            <a href="{{ route('register') }}" class="font-semibold text-[#ffb400] hover:text-[#ff9500] transition-colors duration-200">
                {{ __('auth.create_one') }}
            </a>
        </p>
    </div>
</x-guest-layout>
