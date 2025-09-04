<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Buat Delegasi Baru (Plt./Plh.)') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6">
                    <form action="{{ route('admin.delegations.store') }}" method="POST">
                        @csrf
                        <div class="space-y-6">
                            <div>
                                <label for="jabatan_id" class="block text-sm font-medium text-gray-700">Jabatan yang Didelegasikan</label>
                                <select id="jabatan_id" name="jabatan_id" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md" required>
                                    <option value="">-- Pilih Jabatan --</option>
                                    @foreach ($jabatans as $jabatan)
                                        <option value="{{ $jabatan->id }}" {{ old('jabatan_id') == $jabatan->id ? 'selected' : '' }}>
                                            {{ $jabatan->name }} (Pejabat: {{ $jabatan->user->name }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('jabatan_id') <span class="text-sm text-red-600 mt-1">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label for="user_id" class="block text-sm font-medium text-gray-700">Didelegasikan Kepada</label>
                                <select id="user_id" name="user_id" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md" required>
                                    <option value="">-- Pilih Pegawai --</option>
                                    @foreach ($users as $user)
                                        <option value="{{ $user->id }}" {{ old('user_id') == $user->id ? 'selected' : '' }}>
                                            {{ $user->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('user_id') <span class="text-sm text-red-600 mt-1">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label for="type" class="block text-sm font-medium text-gray-700">Tipe Delegasi</label>
                                <select id="type" name="type" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md" required>
                                    <option value="Plt" {{ old('type') == 'Plt' ? 'selected' : '' }}>Plt (Pelaksana Tugas)</option>
                                    <option value="Plh" {{ old('type') == 'Plh' ? 'selected' : '' }}>Plh (Pelaksana Harian)</option>
                                </select>
                                @error('type') <span class="text-sm text-red-600 mt-1">{{ $message }}</span> @enderror
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label for="start_date" class="block text-sm font-medium text-gray-700">Tanggal Mulai</label>
                                    <input type="date" name="start_date" id="start_date" value="{{ old('start_date') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required>
                                    @error('start_date') <span class="text-sm text-red-600 mt-1">{{ $message }}</span> @enderror
                                </div>
                                <div>
                                    <label for="end_date" class="block text-sm font-medium text-gray-700">Tanggal Selesai</label>
                                    <input type="date" name="end_date" id="end_date" value="{{ old('end_date') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required>
                                    @error('end_date') <span class="text-sm text-red-600 mt-1">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center justify-end mt-8 border-t pt-6">
                            <a href="{{ route('admin.delegations.index') }}" class="text-sm text-gray-600 hover:text-gray-900 mr-4">Batal</a>
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700">
                                Simpan Delegasi
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
