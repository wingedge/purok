<x-guest-layout>
    <div class="flex min-h-screen">
        <div class="hidden lg:flex w-1/2 bg-indigo-700 items-center justify-center p-12 text-white">
            <div class="max-w-xl text-center"> 
                <div class="mb-10 flex justify-center">
                    <a href="/">
                        <x-application-logo class="w-64 h-auto drop-shadow-2xl" />
                    </a>
                </div>
                
                <h1 class="text-5xl font-extrabold mb-6 tracking-tight">Purok Kasadpan</h1>
                <p class="text-xl text-indigo-100 leading-relaxed">
                    Digitizing Local Governance.
                </p>
            </div>
        </div>

        <div class="w-full lg:w-1/2 flex items-center justify-center p-8 bg-white dark:bg-gray-900">
            <div class="w-full max-w-md">
                <div class="lg:hidden mb-12 flex justify-center">
                    <a href="/">
                        <x-application-logo class="w-32 h-auto" />
                    </a>
                </div>

                <h2 class="text-3xl font-extrabold text-gray-900 dark:text-white mb-2">Sign In</h2>
                <p class="text-sm text-gray-600 dark:text-gray-400 mb-8">Please enter your details.</p>

                <x-auth-session-status class="mb-4" :status="session('status')" />

                <form method="POST" action="{{ route('login') }}">
                    @csrf

                    <div>
                        <x-input-label for="email" :value="__('Email')" />
                        <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
                        <x-input-error :messages="$errors->get('email')" class="mt-2" />
                    </div>

                    <div class="mt-4">
                        <x-input-label for="password" :value="__('Password')" />
                        <x-text-input id="password" class="block mt-1 w-full" type="password" name="password" required autocomplete="current-password" />
                        <x-input-error :messages="$errors->get('password')" class="mt-2" />
                    </div>

                    <div class="block mt-4">
                        <label for="remember_me" class="inline-flex items-center">
                            <input id="remember_me" type="checkbox" class="rounded dark:bg-gray-900 border-gray-300 dark:border-gray-700 text-indigo-600 shadow-sm focus:ring-indigo-500 dark:focus:ring-indigo-600 dark:focus:ring-offset-gray-800" name="remember">
                            <span class="ms-2 text-sm text-gray-600 dark:text-gray-400">{{ __('Remember me') }}</span>
                        </label>
                    </div>

                    <div class="flex items-center justify-between mt-6">
                        @if (Route::has('password.request'))
                            <a class="underline text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800" href="{{ route('password.request') }}">
                                {{ __('Forgot your password?') }}
                            </a>
                        @endif

                        <x-primary-button class="ms-3 px-8">
                            {{ __('Log in') }}
                        </x-primary-button>
                    </div>
                </form>

                {{-- @if (Route::has('register'))
                    <div class="mt-8 text-center text-sm text-gray-600 dark:text-gray-400">
                        Don't have an account? 
                        <a href="{{ route('register') }}" class="font-semibold text-indigo-600 hover:text-indigo-500 underline">Create one for free</a>
                    </div>
                @endif --}}
            </div>
        </div>
    </div>
</x-guest-layout>