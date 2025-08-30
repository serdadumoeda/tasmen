<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Arsipkan Surat Masuk Baru') }}
        </h2>
    </x-slot>

    <div class="py-12 bg-gray-50">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6 sm:p-8 text-gray-900">
                    <form action="{{ route('surat-masuk.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="space-y-6">
                            <div>
                                <label for="perihal" class="block font-semibold text-sm text-gray-700 mb-1">Perihal Surat <span class="text-red-500">*</span></label>
                                <input type="text" name="perihal" id="perihal" value="{{ old('perihal') }}" class="mt-1 block w-full rounded-lg shadow-sm border-gray-300 focus:border-indigo-500 focus:ring-indigo-500" required>
                                @error('perihal') <p class="text-sm text-red-600 mt-2">{{ $message }}</p> @enderror
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label for="nomor_surat" class="block font-semibold text-sm text-gray-700 mb-1">Nomor Surat <span class="text-red-500">*</span></label>
                                    <input type="text" name="nomor_surat" id="nomor_surat" value="{{ old('nomor_surat') }}" class="mt-1 block w-full rounded-lg shadow-sm border-gray-300 focus:border-indigo-500 focus:ring-indigo-500" required>
                                    @error('nomor_surat') <p class="text-sm text-red-600 mt-2">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label for="tanggal_surat" class="block font-semibold text-sm text-gray-700 mb-1">Tanggal Surat <span class="text-red-500">*</span></label>
                                    <input type="date" name="tanggal_surat" id="tanggal_surat" value="{{ old('tanggal_surat', date('Y-m-d')) }}" class="mt-1 block w-full rounded-lg shadow-sm border-gray-300 focus:border-indigo-500 focus:ring-indigo-500" required>
                                    @error('tanggal_surat') <p class="text-sm text-red-600 mt-2">{{ $message }}</p> @enderror
                                </div>
                            </div>

                            <div>
                                <label for="lampiran" class="block font-semibold text-sm text-gray-700 mb-1">Upload Pindaian (Scan) Surat <span class="text-red-500">*</span></label>
                                <input id="lampiran" name="lampiran" type="file" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100" required>
                                <p class="text-xs text-gray-500 mt-1">File PDF, JPG, atau PNG. Maksimal 5MB.</p>
                                @error('lampiran') <p class="text-sm text-red-600 mt-2">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        <div class="flex items-center justify-end mt-8 pt-6 border-t border-gray-200">
                            <a href="{{ route('surat-masuk.index') }}" class="text-sm font-medium text-gray-600 hover:text-gray-900 mr-4">Batal</a>
                            <button type="submit" class="inline-flex items-center px-5 py-2.5 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150 shadow-md hover:shadow-lg">
                                <i class="fas fa-archive mr-2"></i> Simpan & Arsipkan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
