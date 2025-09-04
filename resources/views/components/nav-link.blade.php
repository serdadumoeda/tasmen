@props(['active'])

@php
$classes = ($active ?? false)
            // --- KELAS UNTUK LINK AKTIF ---
            ? 'inline-flex items-center px-3 py-2 rounded-md text-sm font-semibold text-indigo-700 bg-indigo-100 focus:outline-none transition duration-150 ease-in-out'
            // --- KELAS UNTUK LINK NORMAL ---
            : 'inline-flex items-center px-3 py-2 rounded-md text-sm font-medium text-gray-500 hover:text-gray-700 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-700 transition duration-150 ease-in-out';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>