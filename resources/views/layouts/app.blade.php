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
    <body class="font-sans antialiased">
        <div class="min-h-screen bg-gray-100">
            @include('layouts.navigation')

            <!-- Page Heading -->
            @isset($header)
                <header class="bg-white shadow">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endisset

            <!-- Page Content -->
            <main class="p-4">
                {{ $slot }}
            </main>
        </div>
    </body>
</html>
