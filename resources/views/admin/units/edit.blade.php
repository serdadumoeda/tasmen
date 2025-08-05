<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Edit Unit') }}
        </h2>
    </x-slot>

    <div class="py-12 bg-gray-50"> {{-- Latar belakang konsisten --}}
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg"> {{-- Shadow dan rounded-lg konsisten --}}
                <div class="p-6 bg-white border-b border-gray-200">
                    <form action="{{ route('admin.units.update', $unit) }}" method="POST">
                        @csrf
                        @method('PUT')
                        @if ($errors->any())
                            <div class="mb-6 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg shadow-md" role="alert">
                                <div class="flex items-start">
                                    <div class="flex-shrink-0 mt-0.5">
                                        <i class="fas fa-exclamation-triangle h-5 w-5 text-red-500"></i>
                                    </div>
                                    <div class="ml-3">
                                        <strong class="font-bold">Oops! Ada yang salah:</strong>
                                        <ul class="mt-1.5 list-disc list-inside text-sm">
                                            @foreach ($errors->all() as $error)
                                                <li>{{ $error }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        @endif
                        {{-- Menggunakan partial form yang sama dengan create --}}
                        @include('admin.units.partials.form-fields', ['unit' => $unit])
                        <div class="flex justify-end mt-8 border-t border-gray-200 pt-6">
                            <button type="submit" class="inline-flex items-center px-5 py-2.5 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150 shadow-md hover:shadow-lg transform hover:scale-105">
                                <i class="fas fa-save mr-2"></i> Perbarui
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Card for Jabatan Management --}}
            <div class="mt-8 bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Daftar Jabatan di Unit Ini</h3>

                    <ul class="space-y-3 mb-6">
                        @forelse($unit->jabatans as $jabatan)
                            <li class="flex items-center justify-between bg-gray-50 p-3 rounded-md">
                                <div>
                                    <p class="font-semibold text-gray-700">{{ $jabatan->name }}</p>
                                    <p class="text-sm text-gray-500">
                                        @if($jabatan->user)
                                            <i class="fas fa-user-check text-green-500 mr-2"></i>Diisi oleh: {{ $jabatan->user->name }}
                                        @else
                                            <i class="fas fa-user-clock text-yellow-500 mr-2"></i>Jabatan Kosong
                                        @endif
                                    </p>
                                </div>
                                @if(!$jabatan->user_id)
                                    <form action="{{ route('admin.jabatans.destroy', $jabatan) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus jabatan ini?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-500 hover:text-red-700 text-sm font-medium">Hapus</button>
                                    </form>
                                @endif
                            </li>
                        @empty
                            <li class="text-center text-gray-500 py-4">Belum ada jabatan yang didefinisikan untuk unit ini.</li>
                        @endforelse
                    </ul>

                    <form action="{{ route('admin.units.jabatans.store', $unit) }}" method="POST" class="border-t border-gray-200 pt-4">
                        @csrf
                        <h4 class="font-semibold text-md text-gray-800 mb-2">Tambah Jabatan Baru</h4>
                        <div class="flex items-center space-x-3">
                            <input type="text" name="name" class="flex-grow mt-1 block w-full rounded-lg shadow-sm border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 transition duration-150" placeholder="e.g., Staf Pelaksana" required>
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700">Tambah</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>