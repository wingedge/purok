<x-guest-layout>
    <div class="min-h-screen flex flex-col justify-center items-center p-6 bg-gradient-to-b from-white to-indigo-50/30">
        
        <x-auth-header title="Officer Login" subtitle="Access Purok Kasadpan community" />

        <div class="w-full sm:max-w-md bg-white p-10 rounded-3xl shadow-xl shadow-indigo-100/50 border border-indigo-50">
            <x-auth-session-status class="mb-4" :status="session('status')" />

            <form method="POST" action="{{ route('login') }}" class="space-y-6">
                @csrf

                <div>
                    <x-input-label for="email" :value="__('Email Address')" class="text-xs font-bold uppercase tracking-widest text-gray-400 mb-1" />
                    <x-text-input id="email" class="block w-full border-gray-200 focus:ring-indigo-500 rounded-xl" type="email" name="email" :value="old('email')" required autofocus />
                    <x-input-error :messages="$errors->get('email')" class="mt-2" />
                </div>

                <div>
                    <div class="flex justify-between mb-1">
                        <x-input-label for="password" :value="__('Password')" class="text-xs font-bold uppercase tracking-widest text-gray-400" />
                        @if (Route::has('password.request'))
                            <a class="text-xs font-bold text-indigo-600 hover:underline" href="{{ route('password.request') }}">Forgot?</a>
                        @endif
                    </div>
                    <x-text-input id="password" class="block w-full border-gray-200 focus:ring-indigo-500 rounded-xl" type="password" name="password" required />
                    <x-input-error :messages="$errors->get('password')" class="mt-2" />
                </div>

                <div class="flex items-center">
                    <input id="remember_me" type="checkbox" name="remember" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                    <span class="ms-2 text-sm text-gray-600">Remember this device</span>
                </div>

                <x-primary-button class="w-full justify-center py-4 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-2xl shadow-lg shadow-indigo-100">
                    {{ __('Sign In to Dashboard') }}
                </x-primary-button>
            </form>
        </div>

        <x-auth-footer />
    </div>
</x-guest-layout>