<x-app-layout>
    {{-- Slot untuk style TomSelect yang sudah diimpor di project.blade.php --}}
    <x-slot name="styles">
        <style>
            /* Style kustom untuk Tom Select (menggunakan tema default) */
            .ts-control {
                border-radius: 0.5rem; /* rounded-lg */
                border-color: #d1d5db; /* gray-300 */
                padding: 0.5rem 0.75rem;
                box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05); /* shadow-sm */
                transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
            }
            .ts-control.focus {
                border-color: #6366f1; /* indigo-500 */
                box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.2); /* ring-indigo-500 */
            }
            .ts-control .item {
                background-color: #00796B; /* Warna hijau gelap */
                color: white;
                border-radius: 0.25rem;
                font-weight: 500;
                padding: 0.25rem 0.5rem;
                margin: 0.125rem;
            }
            .ts-control .item.active {
                background-color: #04655A; /* Sedikit lebih gelap saat aktif */
            }
            .ts-control .remove {
                color: white;
                opacity: 0.8;
            }
            .ts-control .remove:hover {
                color: white;
                opacity: 1;
            }
            .ts-dropdown {
                border-radius: 0.5rem; /* rounded-lg */
                box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05); /* shadow-lg */
            }
            .ts-dropdown .option.active {
                background-color: #e0e7ff; /* indigo-100 */
                color: #1e3a8a; /* indigo-900 */
            }
        </style>
    </x-slot>

    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Edit Proyek: ') }} <span class="font-bold text-indigo-600">{{ $project->name }}</span>
        </h2>
    </x-slot>

    {{-- Latar belakang dan padding konsisten dengan halaman lain --}}
    {{-- Mengubah py-12 menjadi py-8 untuk konsistensi padding vertikal --}}
    <div class="py-8 bg-gray-50"> 
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            {{-- Mengubah shadow-sm menjadi shadow-xl dan memastikan rounded-lg --}}
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <form action="{{ route('projects.update', $project) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        {{-- Gunakan partial form yang sama dengan create --}}
                        {{-- Pastikan projects.partials.form juga sudah di-styling dengan UI terbaru --}}
                        @include('projects.partials.form')

                        <div class="flex items-center justify-between mt-8 border-t border-gray-200 pt-6"> {{-- Menambahkan margin atas, border, dan padding atas --}}
                            <a href="{{ route('projects.show', $project) }}" class="inline-flex items-center text-sm text-gray-600 hover:text-gray-900 font-medium transition-colors duration-200">
                                <i class="fas fa-arrow-left mr-2"></i> Kembali ke Proyek
                            </a>
                            <button type="submit" class="inline-flex items-center px-5 py-2.5 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150 shadow-md hover:shadow-lg transform hover:scale-105">
                                <i class="fas fa-save mr-2"></i> Simpan Perubahan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script> {{-- Pastikan Tom Select di-include --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Inisialisasi TomSelect untuk semua elemen dengan kelas 'tom-select'
            document.querySelectorAll('.tom-select').forEach(element => {
                new TomSelect(element, {
                    plugins: ['remove_button'],
                    create: false,
                    maxItems: null,
                    placeholder: 'Pilih Anggota Tim'
                });
            });
        });
    </script>
    @endpush
</x-app-layout>