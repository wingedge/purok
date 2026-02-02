{{-- resources/views/components/auth-header.blade.php --}}
@props(['title' => 'Sign In', 'subtitle' => 'Access the Purok Kasadpan Portal'])

<div {{ $attributes->merge(['class' => 'mb-8 text-center']) }}>
    <a href="/">
        <x-application-logo class="w-20 h-20 fill-current text-indigo-600 mx-auto transition-transform hover:scale-105" />
    </a>
    <h2 class="mt-6 text-3xl font-black text-gray-900 tracking-tight">
        {{ $title }}
    </h2>
    <p class="text-gray-500 mt-2 font-medium">
        {{ $subtitle }}
    </p>
</div>