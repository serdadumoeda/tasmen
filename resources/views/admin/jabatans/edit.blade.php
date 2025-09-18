<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Edit Jabatan') }}
        </h2>
    </x-slot>

    <div class="py-12 bg-gray-50">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="mb-4">
                <a href="{{ route('admin.units.edit', $jabatan->unit_id) }}" class="inline-flex items-center text-sm font-semibold text-gray-600 hover:text-gray-900">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Kembali ke Edit Unit
                </a>
            </div>
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-8 bg-white border-b border-gray-200">
                    <h3 class="text-xl font-semibold text-gray-800 mb-6">Edit Jabatan: {{ $jabatan->name }}</h3>
                    <form action="{{ route('admin.jabatans.update', $jabatan) }}" method="POST">
                        @csrf
                        @method('PUT')

                        @if ($errors->any())
                            <div class="mb-6 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg shadow-md" role="alert">
                                <strong class="font-bold">Oops! Ada yang salah:</strong>
                                <ul class="mt-1.5 list-disc list-inside text-sm">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <div class="space-y-6">
                            {{-- Nama Jabatan --}}
                            <div>
                                <label for="jabatan_name" class="block text-sm font-medium text-gray-700">Nama Jabatan <span class="text-red-500 font-bold">*</span></label>
                                <input type="text" name="name" id="jabatan_name" value="{{ old('name', $jabatan->name) }}" class="mt-1 block w-full rounded-lg shadow-sm border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 transition duration-150" required>
                                @error('name')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Can Manage Users Checkbox --}}
                            <div class="mt-4">
                                <label for="can_manage_users" class="flex items-center">
                                    <input type="checkbox" name="can_manage_users" id="can_manage_users" value="1" {{ old('can_manage_users', $jabatan->can_manage_users) ? 'checked' : '' }} class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                                    <span class="ml-2 text-sm text-gray-600">Dapat Mengelola Pengguna (Izin Khusus)</span>
                                </label>
                                <p class="mt-1 text-xs text-gray-500 ml-6">Beri izin pada jabatan ini untuk menambah/mengubah pengguna dalam lingkup unitnya.</p>
                                @error('can_manage_users')
                                     <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div class="flex justify-end items-center mt-8 border-t border-gray-200 pt-6">
                            <a href="{{ route('admin.units.edit', $jabatan->unit_id) }}" class="text-sm font-medium text-gray-700 hover:text-gray-900 mr-6">Batal</a>
                            <button type="submit" class="inline-flex items-center px-5 py-2.5 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150 shadow-md hover:shadow-lg transform hover:scale-105">
                                <i class="fas fa-save mr-2"></i> Simpan Perubahan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
