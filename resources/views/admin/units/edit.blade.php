<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Edit Unit') }}
        </h2>
    </x-slot>

    <div class="py-12 bg-gray-50"> {{-- Latar belakang konsisten --}}
        <div class="max-w-screen-2xl mx-auto sm:px-6 lg:px-8">
            <div class="mb-4">
                <a href="javascript:history.back()" class="inline-flex items-center text-sm font-semibold text-gray-600 hover:text-gray-900">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Kembali
                </a>
            </div>
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

            {{-- Card for Temporary Head (Plt./Plh.) Assignment --}}
            @if(!$unit->kepala_unit_id)
            <div class="mt-8 bg-yellow-50 border-2 border-yellow-200 overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-yellow-800 mb-2">
                        <i class="fas fa-user-clock mr-2"></i>Jabatan Kepala Unit Kosong
                    </h3>
                    <p class="text-sm text-yellow-700 mb-4">
                        Unit ini tidak memiliki kepala definitif. Anda dapat menunjuk Pelaksana Tugas (Plt.) atau Pelaksana Harian (Plh.) untuk mengisi posisi ini sementara.
                    </p>
                    <form action="{{ route('admin.units.delegation.store', $unit) }}" method="POST">
                        @csrf
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                            {{-- User Selection --}}
                            <div>
                                <label for="delegation_user_id" class="block text-sm font-medium text-gray-700">Pilih Pengguna <span class="text-red-500">*</span></label>
                                <select name="user_id" id="delegation_user_id" class="mt-1 block w-full rounded-lg shadow-sm border-gray-300 focus:border-indigo-500 focus:ring-indigo-500" required>
                                    <option value="">-- Pilih Pengguna --</option>
                                    @forelse($eligibleDelegates as $user)
                                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                                    @empty
                                        <option value="" disabled>Tidak ada pengguna dengan level yang sama ditemukan.</option>
                                    @endforelse
                                </select>
                            </div>
                             {{-- Type Selection --}}
                            <div>
                                <label for="delegation_type" class="block text-sm font-medium text-gray-700">Tipe <span class="text-red-500">*</span></label>
                                <select name="type" id="delegation_type" class="mt-1 block w-full rounded-lg shadow-sm border-gray-300 focus:border-indigo-500 focus:ring-indigo-500" required>
                                    <option value="Plt">Plt (Pelaksana Tugas)</option>
                                    <option value="Plh">Plh (Pelaksana Harian)</option>
                                </select>
                            </div>
                            {{-- Start Date --}}
                            <div>
                                <label for="delegation_start_date" class="block text-sm font-medium text-gray-700">Tanggal Mulai <span class="text-red-500">*</span></label>
                                <input type="date" name="start_date" id="delegation_start_date" class="mt-1 block w-full rounded-lg shadow-sm border-gray-300 focus:border-indigo-500 focus:ring-indigo-500" required>
                            </div>
                            {{-- End Date --}}
                            <div>
                                <label for="delegation_end_date" class="block text-sm font-medium text-gray-700">Tanggal Selesai <span class="text-red-500">*</span></label>
                                <input type="date" name="end_date" id="delegation_end_date" class="mt-1 block w-full rounded-lg shadow-sm border-gray-300 focus:border-indigo-500 focus:ring-indigo-500" required>
                            </div>
                        </div>
                        <div class="flex justify-end mt-6">
                            <button type="submit" class="inline-flex items-center px-5 py-2.5 bg-yellow-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-yellow-600 focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:ring-offset-2 transition ease-in-out duration-150 shadow-md">
                                <i class="fas fa-user-plus mr-2"></i> Tetapkan Pejabat Sementara
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            @endif

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

                    <form action="{{ route('admin.jabatans.store') }}" method="POST" class="border-t border-gray-200 pt-6">
                        @csrf
                        <input type="hidden" name="unit_id" value="{{ $unit->id }}">
                        <h4 class="font-semibold text-lg text-gray-800 mb-4">Tambah Jabatan Baru</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            {{-- Nama Jabatan --}}
                            <div>
                                <label for="jabatan_name" class="block text-sm font-medium text-gray-700">Nama Jabatan <span class="text-red-500 font-bold">*</span></label>
                                <input type="text" name="name" id="jabatan_name" class="mt-1 block w-full rounded-lg shadow-sm border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 transition duration-150" placeholder="e.g., Pranata Komputer Muda" required>
                            </div>
                            {{-- Role Jabatan --}}
                            <div>
                                <label for="jabatan_role" class="block text-sm font-medium text-gray-700">Peran (Role) <span class="text-red-500 font-bold">*</span></label>
                                <select name="role" id="jabatan_role" class="mt-1 block w-full rounded-lg shadow-sm border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 transition duration-150" required>
                                    @php
                                        // We need the list of roles here. We can get it from the User model.
                                        $availableRoles = \App\Models\User::getAvailableRoles();
                                    @endphp
                                    <option value="">-- Pilih Peran --</option>
                                    @foreach($availableRoles as $role)
                                        <option value="{{ $role }}">{{ $role }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="mt-4">
                             <label for="can_manage_users" class="flex items-center">
                                <input type="checkbox" name="can_manage_users" id="can_manage_users" value="1" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                                <span class="ml-2 text-sm text-gray-600">Dapat Mengelola Pengguna (Izin Khusus)</span>
                            </label>
                             <p class="mt-1 text-xs text-gray-500 ml-6">Beri izin pada jabatan ini (e.g., Kabag Umum) untuk menambah/mengubah pengguna dalam lingkup unitnya.</p>
                        </div>
                        <div class="flex justify-end mt-6">
                            <button type="submit" class="inline-flex items-center px-5 py-2.5 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150 shadow-md">
                                <i class="fas fa-plus-circle mr-2"></i> Tambah Jabatan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>