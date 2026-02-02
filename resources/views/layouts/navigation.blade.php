<nav x-data="{ open: false }" class="bg-white border-b border-gray-100 print:hidden">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}">
                        <x-application-logo class="block h-9 w-auto fill-current text-indigo-600" />
                    </a>
                </div>

                <div class="hidden space-x-4 md:ms-10 md:flex">
                    <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                        {{ __('Dashboard') }}
                    </x-nav-link>

                    <div class="hidden sm:flex sm:items-center">
                        <x-dropdown align="left" width="48">
                            <x-slot name="trigger">
                                <button class="inline-flex items-center px-3 py-2 text-sm font-medium leading-4 text-gray-500 transition duration-150 ease-in-out bg-white hover:text-gray-700 focus:outline-none {{ request()->routeIs('members.*') || request()->routeIs('purok_certificates.*') || request()->routeIs('contributions.*') ? 'text-indigo-600 border-b-2 border-indigo-400' : '' }}">
                                    <div>Community</div>
                                    <div class="ms-1">
                                        <svg class="w-4 h-4 fill-current" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" /></svg>
                                    </div>
                                </button>
                            </x-slot>
                            <x-slot name="content">
                                <x-dropdown-link :href="route('members.index')">Members List</x-dropdown-link>
                                <x-dropdown-link :href="route('contributions.index')">Member Contributions</x-dropdown-link>
                                <x-dropdown-link :href="route('purok_certificates.index')">Purok Certificate Log</x-dropdown-link>
                            </x-slot>
                        </x-dropdown>
                    </div>

                    <div class="hidden sm:flex sm:items-center">
                        <x-dropdown align="left" width="48">
                            <x-slot name="trigger">
                                <button class="inline-flex items-center px-3 py-2 text-sm font-medium leading-4 text-gray-500 transition duration-150 ease-in-out bg-white hover:text-gray-700 focus:outline-none {{ request()->routeIs('incomes.*') || request()->routeIs('expenses.*') ? 'text-indigo-600 border-b-2 border-indigo-400' : '' }}">
                                    <div>Finances</div>
                                    <div class="ms-1">
                                        <svg class="w-4 h-4 fill-current" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" /></svg>
                                    </div>
                                </button>
                            </x-slot>
                            <x-slot name="content">
                                <x-dropdown-link :href="route('incomes.index')">Income Records</x-dropdown-link>
                                <x-dropdown-link :href="route('expenses.index')">Expense Records</x-dropdown-link>
                            </x-slot>
                        </x-dropdown>
                    </div>

                    <div class="hidden sm:flex sm:items-center">
                        <x-dropdown align="left" width="48">
                            <x-slot name="trigger">
                                <button class="inline-flex items-center px-3 py-2 text-sm font-medium leading-4 text-gray-500 transition duration-150 ease-in-out bg-white hover:text-gray-700 focus:outline-none {{ request()->routeIs('inventories.*') || request()->routeIs('rentals.*') ? 'text-indigo-600 border-b-2 border-indigo-400' : '' }}">
                                    <div>Logistics</div>
                                    <div class="ms-1">
                                        <svg class="w-4 h-4 fill-current" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" /></svg>
                                    </div>
                                </button>
                            </x-slot>
                            <x-slot name="content">
                                <x-dropdown-link :href="route('inventories.index')">Inventory Management</x-dropdown-link>
                                <x-dropdown-link :href="route('rentals.index')">Rentals Tracker</x-dropdown-link>
                            </x-slot>
                        </x-dropdown>
                    </div>

                    <x-nav-link href="{{ route('reports.index') }}" :active="request()->routeIs('reports.*')">
                        {{ __('Reports') }}
                    </x-nav-link>
                </div>
            </div>

            <div class="hidden sm:flex sm:items-center sm:ms-6">
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition ease-in-out duration-150">
                            <div class="flex items-center">
                                <div class="h-6 w-6 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-700 text-[10px] font-bold me-2">
                                    {{ substr(Auth::user()->name, 0, 1) }}
                                </div>
                                {{ Auth::user()->name }}
                            </div>
                            <div class="ms-1">
                                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" /></svg>
                            </div>
                        </button>
                    </x-slot>
                    <x-slot name="content">
                        <x-dropdown-link :href="route('profile.edit')">{{ __('Profile') }}</x-dropdown-link>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <x-dropdown-link :href="route('logout')" onclick="event.preventDefault(); this.closest('form').submit();">{{ __('Log Out') }}</x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="..."> </button>
            </div>
        </div>
    </div>

    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden bg-gray-50 border-t border-gray-100">
        <div class="pt-2 pb-3 space-y-1">
            <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">Dashboard</x-responsive-nav-link>
            
            <div class="px-4 py-2 text-xs font-bold text-gray-400 uppercase tracking-widest">Community</div>
            <x-responsive-nav-link href="{{ route('members.index') }}" :active="request()->routeIs('members.*')">Members</x-responsive-nav-link>
            <x-responsive-nav-link href="{{ route('contributions.index') }}" :active="request()->routeIs('contributions.*')">Contributions</x-responsive-nav-link>
            <x-responsive-nav-link href="{{ route('purok_certificates.index') }}" :active="request()->routeIs('purok_certificates.*')">Purok Certificates Log</x-responsive-nav-link>

            <div class="px-4 py-2 text-xs font-bold text-gray-400 uppercase tracking-widest mt-2">Finances</div>
            <x-responsive-nav-link href="{{ route('incomes.index') }}" :active="request()->routeIs('incomes.*')">Incomes</x-responsive-nav-link>
            <x-responsive-nav-link href="{{ route('expenses.index') }}" :active="request()->routeIs('expenses.*')">Expenses</x-responsive-nav-link>
            
            <div class="px-4 py-2 text-xs font-bold text-gray-400 uppercase tracking-widest mt-2">Logistics</div>
            <x-responsive-nav-link href="{{ route('inventories.index') }}" :active="request()->routeIs('inventories.*')">Inventory</x-responsive-nav-link>
            <x-responsive-nav-link href="{{ route('rentals.index') }}" :active="request()->routeIs('rentals.*')">Rentals</x-responsive-nav-link>

            <div class="border-t border-gray-200 mt-2 pt-2">
                <x-responsive-nav-link href="{{ route('reports.index') }}" :active="request()->routeIs('reports.*')">Reports</x-responsive-nav-link>
            </div>
        </div>
        </div>
</nav>