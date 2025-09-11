<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Unggah Surat Baru') }}
        </h2>
    </x-slot>

    <div class="py-12 bg-gray-50">
        <div class="max-w-screen-2xl mx-auto sm:px-6 lg:px-8">
            <div class="mb-4">
                <a href="{{ route('surat.index') }}" class="inline-flex items-center text-sm font-semibold text-gray-600 hover:text-gray-900">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Kembali ke Daftar Surat
                </a>
            </div>
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6 sm:p-8 text-gray-900">
                    <form action="{{ route('surat.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="space-y-6">
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                <div class="md:col-span-2">
                                    <label for="perihal" class="block font-semibold text-sm text-gray-700 mb-1">Perihal Surat <span class="text-red-500">*</span></label>
                                    <input type="text" name="perihal" id="perihal" value="{{ old('perihal') }}" class="mt-1 block w-full rounded-lg shadow-sm border-gray-300 focus:border-indigo-500 focus:ring-indigo-500" required autofocus>
                                    @error('perihal') <p class="text-sm text-red-600 mt-2">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label for="nomor_surat" class="block font-semibold text-sm text-gray-700 mb-1">Nomor Surat (Opsional)</label>
                                    <input type="text" name="nomor_surat" id="nomor_surat" value="{{ old('nomor_surat') }}" class="mt-1 block w-full rounded-lg shadow-sm border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                                    @error('nomor_surat') <p class="text-sm text-red-600 mt-2">{{ $message }}</p> @enderror
                                </div>
                            </div>

                            <div>
                                <label for="tanggal_surat" class="block font-semibold text-sm text-gray-700 mb-1">Tanggal Dokumen Surat <span class="text-red-500">*</span></label>
                                <input type="date" name="tanggal_surat" id="tanggal_surat" value="{{ old('tanggal_surat', date('Y-m-d')) }}" class="mt-1 block w-full rounded-lg shadow-sm border-gray-300 focus:border-indigo-500 focus:ring-indigo-500" required>
                                @error('tanggal_surat') <p class="text-sm text-red-600 mt-2">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label for="file" class="block font-semibold text-sm text-gray-700 mb-1">Upload Dokumen Surat <span class="text-red-500">*</span></label>
                                <input id="file" name="file" type="file" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100" required>
                                <p class="text-xs text-gray-500 mt-1">File PDF, JPG, PNG, DOC, DOCX. Maksimal 10MB.</p>
                                @error('file') <p class="text-sm text-red-600 mt-2">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        <div class="border-t border-gray-200 pt-6 mt-6">
                            <label for="berkas_id" class="block font-semibold text-sm text-gray-700 mb-1">Simpan ke Arsip Digital (Opsional)</label>
                            <select name="berkas_id" id="berkas_id" class="mt-1 block w-full rounded-lg shadow-sm border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">-- Tidak diarsipkan --</option>
                                @isset($berkasList)
                                    @foreach($berkasList as $berkas)
                                        <option value="{{ $berkas->id }}" @selected(old('berkas_id') == $berkas->id)>
                                            {{ $berkas->name }}
                                        </option>
                                    @endforeach
                                @endisset
                            </select>
                            <p class="text-xs text-gray-500 mt-1">Pilih berkas untuk langsung mengarsipkan surat ini setelah dibuat.</p>
                        </div>

                        <div class="flex items-center justify-end mt-8 pt-6 border-t border-gray-200">
                            <a href="{{ route('surat.index') }}" class="text-sm font-medium text-gray-600 hover:text-gray-900 mr-4">Batal</a>
                            <button type="submit" class="inline-flex items-center px-5 py-2.5 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150 shadow-md hover:shadow-lg">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                                </svg>
                                Unggah dan Simpan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
