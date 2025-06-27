@props(['active'])

@php
// --- BAGIAN YANG DIPERBAIKI ---
// Kelas untuk tautan yang TIDAK aktif: Teks diubah menjadi putih solid.
$inactiveClasses = 'inline-flex items-center px-1 pt-1 border-b-[3px] border-transparent text-sm font-medium leading-5 text-white hover:border-yellow-300/75 focus:outline-none focus:text-gray-200 transition duration-150 ease-in-out';

// Kelas untuk tautan yang AKTIF: Warna border diubah menjadi kuning cerah yang kontras dan tebal.
$activeClasses = 'inline-flex items-center px-1 pt-1 border-b-[3px] border-yellow-300 text-white text-sm font-medium leading-5 focus:outline-none transition duration-150 ease-in-out';

$classes = ($active ?? false) ? $activeClasses : $inactiveClasses;
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>