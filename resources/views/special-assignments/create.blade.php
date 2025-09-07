<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Tambah SK Penugasan Baru') }}
        </h2>
    </x-slot>

    {{-- Latar belakang dan padding konsisten dengan halaman lain --}}
    {{-- Mengubah py-12 menjadi py-8 untuk konsistensi padding vertikal --}}
    <div class="py-8"> 
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="mb-4">
                <a href="javascript:history.back()" class="inline-flex items-center text-sm font-semibold text-gray-600 hover:text-gray-900">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Kembali
                </a>
            </div>
            {{-- Mengubah shadow-sm menjadi shadow-xl dan memastikan rounded-lg --}}
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    {{-- PERBAIKAN: Tambahkan enctype untuk upload file --}}
                    <form action="{{ route('special-assignments.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        {{-- Pastikan special-assignments._form.blade.php sudah di-styling dengan UI terbaru --}}
                        {{-- Tombol Simpan/Batal ASUMSIKAN ada di dalam _form.blade.php --}}
                        @include('special-assignments._form', ['assignment' => new \App\Models\SpecialAssignment()])
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>