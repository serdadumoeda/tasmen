<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Tambah Tugas Harian Baru') }}
        </h2>
    </x-slot>

    <div class="py-12 bg-gray-50"> {{-- Menyesuaikan latar belakang dengan Executive Summary dan Adhoc Tasks Index --}}
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg"> {{-- Mengubah shadow-sm menjadi shadow-xl --}}
                <div class="p-6 text-gray-900">
                    {{-- MODIFIKASI: Tambahkan enctype untuk upload file --}}
                    <form action="{{ route('adhoc-tasks.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        
                        {{-- Asumsi _form.blade.php sudah memiliki styling yang konsisten atau akan disesuaikan secara terpisah --}}
                        @include('adhoc-tasks._form')

                        <div class="flex items-center justify-end mt-8 border-t border-gray-200 pt-6"> {{-- Menambahkan margin atas, border, dan padding atas --}}
                            <a href="{{ route('adhoc-tasks.index') }}" class="text-sm text-gray-600 hover:text-gray-900 font-medium mr-6 transition-colors duration-200"> {{-- Meningkatkan margin, menambahkan font-medium dan transisi --}}
                                Batal
                            </a>
                            <button type="submit" class="inline-flex items-center px-5 py-2.5 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150 shadow-md hover:shadow-lg transform hover:scale-105"> {{-- Menyesuaikan padding, warna, dan menambahkan shadow serta efek hover --}}
                                <i class="fas fa-save mr-2"></i> Simpan Tugas
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @push('styles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.min.css">
    <style>
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
            background-color: #4f46e5; /* indigo-600 */
            color: white;
            border-radius: 0.25rem;
            font-weight: 500;
            padding: 0.25rem 0.5rem;
            margin: 0.125rem;
        }
        .ts-control .item.active {
            background-color: #4338ca; /* indigo-700 */
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
    @endpush

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const selectElement = document.getElementById('assignees');
            if (selectElement) {
                new TomSelect(selectElement, {
                    plugins: ['remove_button'],
                    create: false,
                    maxItems: null,
                    placeholder: 'Pilih Anggota Tim',
                    items: @json(old('assignees', []))
                });
            }
        });
    </script>
    @endpush
</x-app-layout>