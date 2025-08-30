<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Edit Template Surat') }}
        </h2>
    </x-slot>

    <div class="py-12 bg-gray-50">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6 sm:p-8 text-gray-900">
                    <form action="{{ route('templatesurat.update', $templatesurat) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="space-y-6">
                            <div>
                                <label for="judul" class="block font-semibold text-sm text-gray-700 mb-1">Judul Template <span class="text-red-500">*</span></label>
                                <input type="text" name="judul" id="judul" value="{{ old('judul', $templatesurat->judul) }}" class="mt-1 block w-full rounded-lg shadow-sm border-gray-300 focus:border-indigo-500 focus:ring-indigo-500" required>
                                @error('judul') <p class="text-sm text-red-600 mt-2">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label for="deskripsi" class="block font-semibold text-sm text-gray-700 mb-1">Deskripsi (Opsional)</label>
                                <textarea name="deskripsi" id="deskripsi" rows="3" class="mt-1 block w-full rounded-lg shadow-sm border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">{{ old('deskripsi', $templatesurat->deskripsi) }}</textarea>
                                @error('deskripsi') <p class="text-sm text-red-600 mt-2">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label for="konten" class="block font-semibold text-sm text-gray-700 mb-1">Konten Template <span class="text-red-500">*</span></label>
                                <p class="text-xs text-gray-500 mb-2">Gunakan placeholder seperti <code>{{ '{nama_penerima}' }}</code> atau <code>{{ '{tanggal_surat}' }}</code>. Placeholder ini akan diganti dengan data sebenarnya saat surat dibuat.</p>
                                <textarea name="konten" id="konten" rows="15" class="mt-1 block w-full rounded-lg shadow-sm border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 font-mono">{{ old('konten', $templatesurat->konten) }}</textarea>
                                @error('konten') <p class="text-sm text-red-600 mt-2">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        <div class="flex items-center justify-end mt-8 pt-6 border-t border-gray-200">
                            <a href="{{ route('templatesurat.index') }}" class="text-sm font-medium text-gray-600 hover:text-gray-900 mr-4">Batal</a>
                            <button type="submit" class="inline-flex items-center px-5 py-2.5 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150 shadow-md hover:shadow-lg">
                                <i class="fas fa-save mr-2"></i> Perbarui Template
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    @push('scripts')
        <script src="https://cdn.tiny.cloud/1/{{ config('services.tinymce.api_key', 'no-api-key') }}/tinymce/7/tinymce.min.js" referrerpolicy="origin"></script>
        <script>
            tinymce.init({
                selector: 'textarea#konten',
                plugins: 'code table lists wordcount',
                toolbar: 'undo redo | blocks | bold italic | alignleft aligncenter alignright | indent outdent | bullist numlist | code | table'
            });
        </script>
    @endpush
</x-app-layout>
