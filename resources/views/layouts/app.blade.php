<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Tasmen') }}</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    {{-- Font Awesome untuk ikon --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    {{ $styles ?? '' }}
</head>
<body class="font-sans antialiased">
    <div class="min-h-screen bg-gray-100 flex flex-col">
        
        <header class="sticky top-0 z-20">
            @include('layouts.navigation')

            @if (isset($header))
                <div class="bg-white shadow">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </div>
            @endif
        </header>

        <main class="flex-grow">
            {{ $slot }}
        </main>
        
        {{-- Footer dipindahkan ke sini agar lebarnya penuh --}}
        <footer class="text-center py-4 text-sm text-gray-500 border-t border-gray-200 bg-gray-100">
            © {{ date('Y') }} {{ config('app.name', 'Tasmen') }}. All Rights Reserved.
        </footer>
    </div>

    {{-- Pustaka SweetAlert2 untuk notifikasi --}}
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    {{-- Script untuk menampilkan notifikasi dari session flash --}}
    @if (session('success') || session('error') || session('info'))
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const Toast = Swal.mixin({
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3500,
                timerProgressBar: true,
                didOpen: (toast) => {
                    toast.addEventListener('mouseenter', Swal.stopTimer)
                    toast.addEventListener('mouseleave', Swal.resumeTimer)
                }
            });

            @if ($message = session('success'))
                Toast.fire({ icon: 'success', title: '{{ $message }}' });
            @endif

            @if ($message = session('error'))
                Toast.fire({ icon: 'error', title: '{{ $message }}' });
            @endif

            @if ($message = session('info'))
                Toast.fire({ icon: 'info', title: '{{ $message }}' });
            @endif
        });
    </script>
    @endif
    
    {{-- Slot untuk script tambahan per halaman --}}
    {{ $scripts ?? '' }}
</body>
</html>