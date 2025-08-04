<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Tasmen') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer" />
    {{-- Select2 CSS --}}
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    {{-- PERBAIKAN: Tambahkan aturan CSS untuk x-cloak di sini --}}
    <style>
        [x-cloak] { display: none !important; }
    </style>

    {{ $styles ?? '' }}
</head>
<body class="font-sans antialiased">
    <div class="min-h-screen bg-gray-100 flex flex-col">
        @include('layouts.navigation')

        @if (isset($header))
            <header class="bg-white shadow-2xl">
                <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                    {{ $header }}
                </div>
            </header>
        @endif

        <main class="flex-grow bg-gray-50">
            {{ $slot }}
        </main>

        @include('partials.about-us-modal') 

        <footer class="w-full text-center py-4 bg-gray-100 border-t border-gray-200 text-gray-500 text-sm">
            &copy; Konsep & Visualisasi oleh <span class="font-bold text-indigo-700">PSI 2025</span>
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
    @if (session('success') || session('error') || session('info') || session('warning'))
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const Toast = Swal.mixin({
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 4500, // Waktu lebih lama untuk pesan dengan peringatan
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
            @if ($message = session('warning'))
                Toast.fire({ icon: 'warning', title: '{{ $message }}' });
            @endif
            @if ($message = session('info'))
                Toast.fire({ icon: 'info', title: '{{ $message }}' });
            @endif
        });
    </script>
    @endif
    
    {{-- Slot untuk script tambahan per halaman --}}
    @stack('scripts')

    {{-- jQuery (dependency for Select2) --}}
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    {{-- Select2 JS --}}
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script>
        // Inisialisasi global untuk semua elemen dengan kelas .select2-searchable
        $(document).ready(function() {
            $('.select2-searchable').select2({
                theme: "classic",
                width: '100%'
            });
        });
    </script>
</body>
</html>