<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Edit SK Penugasan') }}
        </h2>
    </x-slot>

    {{-- Latar belakang dan padding konsisten dengan halaman lain --}}
    <div class="py-8"> 
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            {{-- Bayangan dan sudut membulat konsisten --}}
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    {{-- PERBAIKAN: Tambahkan enctype untuk upload file --}}
                    <form action="{{ route('special-assignments.update', $assignment) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        
                        {{-- Pastikan special-assignments._form.blade.php sudah di-styling dengan UI terbaru --}}
                        {{-- Tombol Simpan/Batal ASUMSIKAN ada di dalam _form.blade.php --}}
                        @include('special-assignments._form')

                        {{-- BAGIAN INI DIHAPUS UNTUK MENGATASI DUPLIKASI --}}
                        {{--
                        <div class="flex items-center justify-end mt-8 border-t border-gray-200 pt-6">
                            <a href="{{ route('special-assignments.index') }}" class="text-sm text-gray-600 hover:text-gray-900 font-medium mr-6 transition-colors duration-200">
                                Batal
                            </a>
                            <button type="submit" class="inline-flex items-center px-5 py-2.5 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150 shadow-md hover:shadow-lg transform hover:scale-105">
                                <i class="fas fa-save mr-2"></i> Simpan Perubahan
                            </button>
                        </div>
                        --}}
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>