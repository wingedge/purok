<x-guest-layout>
    <div class="min-h-screen flex flex-col justify-center items-center p-6 bg-gradient-to-b from-white to-indigo-50/30">
        
        <x-auth-header title="Officer Login" subtitle="Access Purok Kasadpan community" />

        <div class="w-full sm:max-w-md bg-white p-10 rounded-3xl shadow-xl shadow-indigo-100/50 border border-indigo-50">
            
            <div class="mb-4 text-sm text-gray-600">
                {{ __('Thanks for signing up! Before getting started, could you verify your email address by clicking on the link we just emailed to you? If you didn\'t receive the email, we will gladly send you another.') }}
            </div>

            @if (session('status') == 'verification-link-sent')
                <div class="mb-4 font-medium text-sm text-green-600">
                    {{ __('A new verification link has been sent to the email address you provided during registration.') }}
                </div>
            @endif

            <div class="mt-4 flex items-center justify-between">
                <form method="POST" action="{{ route('verification.send') }}">
                    @csrf

                    <div>
                        <x-primary-button>
                            {{ __('Resend Verification Email') }}
                        </x-primary-button>
                    </div>
                </form>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf

                    <button type="submit" class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        {{ __('Log Out') }}
                    </button>
                </form>
            </div>


        </div>

        <x-auth-footer />
    </div>
</x-guest-layout>
