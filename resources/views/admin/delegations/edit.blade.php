<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Edit Delegasi') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <form action="{{ route('admin.delegasi.update', $delegasi) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="jabatan_id" class="block font-medium text-sm text-gray-700">Jabatan yang Didelegasikan</label>
                                <select name="jabatan_id" id="jabatan_id" class="block mt-1 w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" required>
                                    @foreach($jabatans as $jabatan)
                                        <option value="{{ $jabatan->id }}" @selected(old('jabatan_id', $delegasi->jabatan_id) == $jabatan->id)>{{ $jabatan->nama_jabatan }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label for="user_id" class="block font-medium text-sm text-gray-700">User Penerima Delegasi</label>
                                <select name="user_id" id="user_id" class="block mt-1 w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" required>
                                    @foreach($users as $user)
                                        <option value="{{ $user->id }}" @selected(old('user_id', $delegasi->user_id) == $user->id)>{{ $user->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label for="jenis" class="block font-medium text-sm text-gray-700">Jenis Delegasi</label>
                                <select name="jenis" id="jenis" class="block mt-1 w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" required>
                                    <option value="Plt" @selected(old('jenis', $delegasi->jenis) == 'Plt')>Plt (Pelaksana Tugas)</option>
                                    <option value="Plh" @selected(old('jenis', $delegasi->jenis) == 'Plh')>Plh (Pelaksana Harian)</option>
                                </select>
                            </div>
                            <div>
                                <label for="keterangan" class="block font-medium text-sm text-gray-700">Keterangan</label>
                                <textarea name="keterangan" id="keterangan" rows="3" class="block mt-1 w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">{{ old('keterangan', $delegasi->keterangan) }}</textarea>
                            </div>
                             <div>
                                <label for="tanggal_mulai" class="block font-medium text-sm text-gray-700">Tanggal Mulai</label>
                                <input type="date" name="tanggal_mulai" id="tanggal_mulai" value="{{ old('tanggal_mulai', $delegasi->tanggal_mulai->format('Y-m-d')) }}" class="block mt-1 w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" required>
                            </div>
                            <div>
                                <label for="tanggal_selesai" class="block font-medium text-sm text-gray-700">Tanggal Selesai</label>
                                <input type="date" name="tanggal_selesai" id="tanggal_selesai" value="{{ old('tanggal_selesai', $delegasi->tanggal_selesai->format('Y-m-d')) }}" class="block mt-1 w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" required>
                            </div>
                        </div>

                        <div class="flex items-center justify-end mt-6">
                            <a href="{{ route('admin.delegasi.index') }}" class="text-sm text-gray-600 hover:text-gray-900 mr-4">Batal</a>
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                                Simpan Perubahan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
