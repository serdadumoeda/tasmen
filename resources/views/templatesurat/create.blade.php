<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Buat Template Surat Baru') }}
        </h2>
    </x-slot>

    <div class="py-12 bg-gray-50">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6 sm:p-8 text-gray-900">
                    <form action="{{ route('templatesurat.store') }}" method="POST">
                        @csrf
                        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                            {{-- Kolom Utama (Kiri) --}}
                            <div class="lg:col-span-2 space-y-6">
                                <div>
                                    <label for="judul" class="block font-semibold text-sm text-gray-700 mb-1">Judul Template <span class="text-red-500">*</span></label>
                                    <input type="text" name="judul" id="judul" value="{{ old('judul') }}" class="mt-1 block w-full rounded-lg shadow-sm border-gray-300 focus:border-indigo-500 focus:ring-indigo-500" required>
                                    @error('judul') <p class="text-sm text-red-600 mt-2">{{ $message }}</p> @enderror
                                </div>

                                <div>
                                    <label for="deskripsi" class="block font-semibold text-sm text-gray-700 mb-1">Deskripsi (Opsional)</label>
                                    <textarea name="deskripsi" id="deskripsi" rows="3" class="mt-1 block w-full rounded-lg shadow-sm border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">{{ old('deskripsi') }}</textarea>
                                    @error('deskripsi') <p class="text-sm text-red-600 mt-2">{{ $message }}</p> @enderror
                                </div>

                                <div>
                                    <label for="konten" class="block font-semibold text-sm text-gray-700 mb-1">Konten Template <span class="text-red-500">*</span></label>
                                    <textarea name="konten" id="konten" rows="20" class="mt-1 block w-full rounded-lg shadow-sm border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 font-mono">{{ old('konten') }}</textarea>
                                    @error('konten') <p class="text-sm text-red-600 mt-2">{{ $message }}</p> @enderror
                                </div>
                            </div>

                            {{-- Kolom Samping (Kanan) --}}
                            <div class="space-y-6">
                                <div class="p-4 bg-indigo-50 border border-indigo-200 rounded-lg shadow-sm">
                                    <h4 class="font-bold text-gray-800 mb-2">Bantuan Placeholder</h4>
                                    <p class="text-xs text-gray-600 mb-2">
                                        Gunakan placeholder untuk bagian surat yang akan diisi secara dinamis. Cukup ketik nama placeholder (tanpa spasi, gunakan underscore jika perlu) lalu klik "Sisipkan".
                                    </p>
                                    <div class="space-y-2">
                                        <label for="placeholder_name" class="sr-only">Nama Placeholder</label>
                                        <input type="text" id="placeholder_name" placeholder="cth: nama_kegiatan" class="block w-full text-sm rounded-md border-gray-300 shadow-sm">
                                        <button type="button" id="insert_placeholder_btn" class="w-full inline-flex justify-center items-center px-3 py-2 bg-indigo-500 text-white text-xs font-semibold rounded-md hover:bg-indigo-600">
                                            <i class="fas fa-plus-circle mr-2"></i> Sisipkan Placeholder
                                        </button>
                                    </div>
                                </div>
                                <div class="p-4 bg-gray-50 border border-gray-200 rounded-lg shadow-sm">
                                     <h4 class="font-bold text-gray-800 mb-2">Contoh</h4>
                                     <p class="text-xs text-gray-600">
                                         Menugaskan Sdr. <code class="bg-gray-200 text-red-600 p-0.5 rounded">@{{nama_pegawai}}</code> untuk mengikuti kegiatan...
                                     </p>
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center justify-end mt-8 pt-6 border-t border-gray-200">
                            <a href="{{ route('templatesurat.index') }}" class="text-sm font-medium text-gray-600 hover:text-gray-900 mr-4">Batal</a>
                            <button type="submit" class="inline-flex items-center px-5 py-2.5 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150 shadow-md hover:shadow-lg">
                                <i class="fas fa-save mr-2"></i> Simpan Template
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
            document.addEventListener('DOMContentLoaded', function () {
                tinymce.init({
                    selector: 'textarea#konten',
                    plugins: 'code table lists wordcount',
                    toolbar: 'undo redo | blocks | bold italic | alignleft aligncenter alignright | indent outdent | bullist numlist | code | table',
                    setup: function (editor) {
                        document.getElementById('insert_placeholder_btn').addEventListener('click', function () {
                            const placeholderName = document.getElementById('placeholder_name').value;
                            if (placeholderName) {
                                // Sanitize placeholder name to be safe (alphanumeric and underscores)
                                const sanitized = placeholderName.replace(/[^a-zA-Z0-9_]/g, '');
                                if (sanitized) {
                                    editor.execCommand('mceInsertContent', false, `{{${sanitized}}}`);
                                } else {
                                    alert('Nama placeholder hanya boleh berisi huruf, angka, dan underscore.');
                                }
                                document.getElementById('placeholder_name').value = ''; // Clear input
                            }
                        });
                    }
                });
            });
        </script>
    @endpush
</x-app-layout>
