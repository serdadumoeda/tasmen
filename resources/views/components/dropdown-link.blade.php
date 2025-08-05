@props(['active' => false])

@php
// --- BAGIAN YANG DIPERBAIKI ---
// Kelas dasar untuk setiap item
$baseClasses = 'block w-full px-4 py-2 text-start text-sm leading-5 text-gray-700 transition duration-150 ease-in-out';

// Kelas saat item di-hover atau aktif
$activeClasses = ' hover:bg-green-50 focus:outline-none focus:bg-green-100';

$classes = $baseClasses . $activeClasses;
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>{{ $slot }}</a>