<x-guest-layout>
    <div class="flex flex-col lg:flex-row min-h-screen h-full">
        
        <div class="w-full lg:w-1/2 bg-indigo-700 flex items-center justify-center p-12 lg:p-20 text-white relative overflow-hidden">
            {{-- Subtle decoration so it doesn't look flat --}}
            <div class="absolute -top-24 -left-24 w-64 h-64 bg-indigo-600 rounded-full opacity-50 blur-3xl"></div>
            
            <div class="max-w-md text-center lg:text-left relative z-10">
                <x-application-logo class="w-20 h-auto text-white fill-current mb-8 mx-auto lg:mx-0" />
                <h1 class="text-5xl lg:text-6xl font-black leading-tight tracking-tighter mb-6">
                    Purok <br class="hidden lg:block"/><span class="text-indigo-300">Kasadpan</span>
                </h1>
                <p class="text-lg text-indigo-100 font-light leading-relaxed">
                    Digital records, transparent finances, and better community management for our citizens.
                </p>
            </div>
        </div>

        <div class="w-full lg:w-1/2 bg-white flex items-center justify-center p-8 lg:p-20">
            <div class="w-full max-w-sm space-y-10">
                <div class="text-center lg:text-left">
                    <h2 class="text-3xl font-extrabold text-gray-900 tracking-tight">Portal Access</h2>
                    <p class="text-gray-500 mt-2 font-medium">Choose an option below to continue.</p>
                </div>

                <div class="space-y-4">
                    <a href="{{ route('login') }}" class="group flex items-center p-4 bg-white border-2 border-gray-100 rounded-2xl hover:border-indigo-600 hover:bg-indigo-50 transition-all duration-300">
                        <div class="p-3 bg-indigo-100 text-indigo-600 rounded-xl group-hover:bg-indigo-600 group-hover:text-white transition-colors">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" /></svg>
                        </div>
                        <div class="ms-4 text-left">
                            <p class="text-base font-bold text-gray-900">Sign In</p>
                            <p class="text-xs text-gray-500">Access administrator dashboard</p>
                        </div>
                    </a>

                    @if (Route::has('register'))
                    <a href="{{ route('register') }}" class="group flex items-center p-4 bg-white border-2 border-gray-100 rounded-2xl hover:border-indigo-600 hover:bg-indigo-50 transition-all duration-300">
                        <div class="p-3 bg-gray-100 text-gray-600 rounded-xl group-hover:bg-indigo-600 group-hover:text-white transition-colors">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" /></svg>
                        </div>
                        <div class="ms-4 text-left">
                            <p class="text-base font-bold text-gray-900">Register</p>
                            <p class="text-xs text-gray-500">Create a new officer account</p>
                        </div>
                    </a>
                    @endif
                </div>

                <div class="pt-8 text-center lg:text-left">
                    <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest">
                        System Architecture by [Your Name]
                    </p>
                </div>
            </div>
        </div>
    </div>
</x-guest-layout>