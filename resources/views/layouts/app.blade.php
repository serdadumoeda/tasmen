<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $pageTitle ?? config('app.name', 'Tasmen') }}</title>
    <link rel="icon" href="{{ asset('images/logo-kemnaker.png') }}" type="image/png">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    {{-- PERBAIKAN: Tambahkan aturan CSS untuk x-cloak di sini --}}
    <style>
        [x-cloak] { display: none !important; }
    </style>

    {{-- Flatpickr CSS --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">

    {{ $styles ?? '' }}
</head>
<body class="font-sans antialiased">
    <div class="min-h-screen bg-gray-100 flex flex-col">
        @include('layouts.navigation')

        @if (session()->has('impersonator_id'))
            <div class="w-full bg-yellow-400 text-center py-2 text-sm font-semibold text-yellow-800 border-b-2 border-yellow-500">
                Anda sedang meniru pengguna lain.
                <a href="{{ route('admin.users.impersonate.leave') }}" class="font-bold underline hover:text-yellow-900">Kembali ke akun Anda</a>.
            </div>
        @endif

        @if (isset($header))
            <header class="bg-white shadow-2xl">
                <div class="max-w-screen-2xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                    {{ $header }}
                </div>
            </header>
        @endif

        {{-- Breadcrumb Navigation --}}
        <div class="bg-white shadow-sm border-t border-gray-200">
            <div class="max-w-screen-2xl mx-auto py-2 px-4 sm:px-6 lg:px-8">
                <x-breadcrumbs />
            </div>
        </div>

        <main class="flex-grow bg-gray-50">
            {{ $slot }}
        </main>

        @include('partials.about-us-modal') 

        <a href="https://docs.google.com/forms/d/e/1FAIpQLSd8NwcrLRq5B5nBMUAUw6z498diaXK4QWxnzkkfa91wNscy5Q/viewform"
           target="_blank" rel="noopener"
           class="fixed bottom-6 right-6 inline-flex items-center gap-2 rounded-full bg-yellow-400 text-gray-900 px-5 py-3 font-semibold shadow-xl transition hover:-translate-y-1 hover:shadow-2xl focus:outline-none focus:ring-4 focus:ring-yellow-300">
            <i class="fas fa-bullhorn text-lg"></i>
            <span>Laporkan!</span>
        </a>

        <footer class="w-full text-center py-4 bg-gray-100 border-t border-gray-200 text-gray-500 text-sm">
            &copy; Pusdatik - <span class="font-bold text-indigo-700">2025</span>
        </footer>
    </div>

    {{-- Pustaka SweetAlert2 untuk notifikasi --}}
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    {{-- Script untuk komponen Notifikasi --}}
    <script>
        function notifications() {
            return {
                isOpen: false,
                unread: [],
                count: 0,
                fetchUnread() {
                    fetch('{{ route("notifications.unread") }}')
                        .then(response => response.json())
                        .then(data => {
                            this.unread = data.unread;
                            this.count = data.count;
                        });
                },
                markAsRead(notificationId) {
                    fetch('{{ route("notifications.markAsRead") }}', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({ id: notificationId })
                    });
                }
            }
        }
    </script>
    
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
    
    {{-- jQuery --}}
    <script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>

    {{-- Flatpickr JS --}}
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

    {{-- Slot untuk script tambahan per halaman --}}
    @stack('scripts')
</body>
</html>
