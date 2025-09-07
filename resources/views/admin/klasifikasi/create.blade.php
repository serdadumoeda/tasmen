<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Tambah Klasifikasi Surat Baru') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <form action="{{ route('admin.klasifikasi.store') }}" method="POST">
                        @csrf
                        <div class="space-y-6">
                            <div>
                                <label for="kode" class="block font-semibold text-sm text-gray-700 mb-1">Kode Klasifikasi <span class="text-red-500">*</span></label>
                                <input type="text" name="kode" id="kode" value="{{ old('kode') }}" class="mt-1 block w-full rounded-lg shadow-sm border-gray-300 focus:border-indigo-500 focus:ring-indigo-500" required>
                                @error('kode') <p class="text-sm text-red-600 mt-2">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label for="deskripsi" class="block font-semibold text-sm text-gray-700 mb-1">Deskripsi <span class="text-red-500">*</span></label>
                                <input type="text" name="deskripsi" id="deskripsi" value="{{ old('deskripsi') }}" class="mt-1 block w-full rounded-lg shadow-sm border-gray-300 focus:border-indigo-500 focus:ring-indigo-500" required>
                                @error('deskripsi') <p class="text-sm text-red-600 mt-2">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label for="parent_id" class="block font-semibold text-sm text-gray-700 mb-1">Induk Klasifikasi (Opsional)</label>
                                <select name="parent_id" id="parent_id" class="mt-1 block w-full rounded-lg shadow-sm border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="">Tidak ada induk</option>
                                    @foreach($parents as $parent)
                                        <option value="{{ $parent->id }}" {{ old('parent_id') == $parent->id ? 'selected' : '' }}>
                                            {{ $parent->kode }} - {{ $parent->deskripsi }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('parent_id') <p class="text-sm text-red-600 mt-2">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        <div class="flex items-center justify-end mt-8 pt-6 border-t border-gray-200">
                            <a href="{{ route('admin.klasifikasi.index') }}" class="text-sm font-medium text-gray-600 hover:text-gray-900 mr-4">Batal</a>
                            <button type="submit" class="inline-flex items-center px-5 py-2.5 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150 shadow-md">
                                Simpan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
