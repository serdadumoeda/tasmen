<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Edit Tugas Harian') }}
        </h2>
    </x-slot>

    <div class="py-12 bg-gray-50">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    {{-- Form action menunjuk ke route update dengan method PATCH --}}
                    <form action="{{ route('adhoc-tasks.update', $task) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PATCH')

                        {{-- Include partial form, dengan meneruskan data $task --}}
                        {{-- Variabel $task akan otomatis tersedia di dalam _form.blade.php --}}
                        @include('adhoc-tasks._form')

                        <div class="flex items-center justify-end mt-8 border-t border-gray-200 pt-6">
                            <a href="{{ route('adhoc-tasks.index') }}" class="text-sm text-gray-600 hover:text-gray-900 font-medium mr-6 transition-colors duration-200">
                                Batal
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

    @push('styles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.bootstrap5.min.css">
    @endpush
    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            new TomSelect('#assignees', {
                plugins: ['remove_button'],
                create: false,
                maxItems: null,
                placeholder: 'Pilih Anggota Tim'
            });
        });
    </script>
    @endpush
</x-app-layout>
