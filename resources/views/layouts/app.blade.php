<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <style>
            @media print {

                /* Hide navigation, buttons, filters */
                nav,
                header,
                .print-hidden,
                button,
                form {
                    display: none !important;
                }

                /* Remove layout padding */
                body {
                    background: white !important;
                }

                main {
                    padding: 0 !important;
                }

                /* Full width content */
                .max-w-5xl,
                .max-w-7xl {
                    max-width: 100% !important;
                }

                /* Table styling */
                table {
                    width: 100%;
                    border-collapse: collapse;
                }

                td {
                    padding: 8px 12px;
                    border-bottom: 1px solid #ddd;
                }

                .bg-gray-50,
                .bg-gray-100 {
                    background: #f5f5f5 !important;
                    -webkit-print-color-adjust: exact;
                }

                /* Ensure colors print */
                * {
                    -webkit-print-color-adjust: exact;
                    print-color-adjust: exact;
                }
            }
        </style>


    </head>
    <body class="font-sans antialiased text-gray-900">
        <div class="min-h-screen bg-gray-50/50"> {{-- Lighter background for better contrast --}}
            @include('layouts.navigation')

            @isset($header)
                <header class="bg-white border-b border-gray-200"> {{-- Border looks cleaner than shadow --}}
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        <div class="flex flex-col md:flex-row md:items-center md:justify-between space-y-2 md:space-y-0">
                            
                            <div>
                                <div class="flex items-center space-x-2">
                                    {{-- Optional: Small dynamic icon based on current time --}}
                                    <span class="text-indigo-600">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7"></path></svg>
                                    </span>
                                    <h1 class="text-2xl font-extrabold text-gray-900 tracking-tight">
                                        {{ $header }}
                                    </h1>
                                </div>
                                <p class="mt-1 text-sm text-gray-500 font-medium">
                                    {{ now()->format('l, F j, Y') }} {{-- Dynamic Date --}}
                                </p>
                            </div>

                            <div class="hidden md:block text-right">
                                <span class="text-sm text-gray-400">Welcome back,</span>
                                <span class="block text-sm font-bold text-gray-700">{{ Auth::user()->name }}</span>
                            </div>

                        </div>
                    </div>
                </header>
            @endisset

          
            <main class="py-10 animate-in fade-in duration-500">
                <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    {{ $slot }}
                </div>
            </main>

            <footer class="py-6 border-t border-gray-200 mt-auto print:hidden">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="flex flex-col items-center justify-center space-y-2 md:flex-row md:justify-between md:space-y-0">
                        <p class="text-xs text-gray-400 font-medium">
                            &copy; {{ date('Y') }} Purok Management System. All rights reserved.
                        </p>
                        <div class="flex items-center space-x-1">
                            <span class="text-xs text-gray-400">Developed by</span>                            
                            <span class="text-xs font-bold text-gray-600 hover:text-indigo-600 transition-colors">
                                Francis Moreno
                            </span>
                        </div>
                    </div>
                </div>
            </footer>
        </div>
    </body>
</html>
