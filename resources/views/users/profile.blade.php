<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('User Profile') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-screen-2xl mx-auto sm:px-6 lg:px-8">
            <div class="mb-4">
                <a href="javascript:history.back()" class="inline-flex items-center text-sm font-semibold text-gray-600 hover:text-gray-900">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Kembali
                </a>
            </div>
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-2xl font-bold text-gray-900">{{ $user->name }}</h3>
                        <a href="{{ route('users.edit', $user) }}" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-900 focus:outline-none focus:border-gray-900 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150">
                            Edit User
                        </a>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">

                        {{-- Personal Information --}}
                        <div class="md:col-span-1">
                            <h4 class="text-lg font-medium text-gray-800 border-b pb-2 mb-4">Informasi Pribadi</h4>
                            <dl class="space-y-2">
                                <div class="flex justify-between">
                                    <dt class="font-semibold text-sm text-gray-600">NIP:</dt>
                                    <dd class="text-sm text-gray-900">{{ $user->nip ?? '-' }}</dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt class="font-semibold text-sm text-gray-600">Email:</dt>
                                    <dd class="text-sm text-gray-900">{{ $user->email }}</dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt class="font-semibold text-sm text-gray-600">Tempat, Tgl. Lahir:</dt>
                                    <dd class="text-sm text-gray-900">{{ $user->tempat_lahir ?? '-' }}, {{ $user->tgl_lahir ? \Carbon\Carbon::parse($user->tgl_lahir)->format('d M Y') : '-' }}</dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt class="font-semibold text-sm text-gray-600">Jenis Kelamin:</dt>
                                    <dd class="text-sm text-gray-900">{{ $user->jenis_kelamin == 'L' ? 'Laki-laki' : 'Perempuan' }}</dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt class="font-semibold text-sm text-gray-600">Agama:</dt>
                                    <dd class="text-sm text-gray-900">{{ $user->agama ?? '-' }}</dd>
                                </div>
                                <div class="flex flex-col">
                                    <dt class="font-semibold text-sm text-gray-600 mb-1">Alamat:</dt>
                                    <dd class="text-sm text-gray-900">{{ $user->alamat ?? '-' }}</dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt class="font-semibold text-sm text-gray-600">No. HP:</dt>
                                    <dd class="text-sm text-gray-900">{{ $user->no_hp ?? '-' }}</dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt class="font-semibold text-sm text-gray-600">Telepon:</dt>
                                    <dd class="text-sm text-gray-900">{{ $user->telepon ?? '-' }}</dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt class="font-semibold text-sm text-gray-600">NPWP:</dt>
                                    <dd class="text-sm text-gray-900">{{ $user->npwp ?? '-' }}</dd>
                                </div>
                            </dl>
                        </div>

                        {{-- Employment Information --}}
                        <div class="md:col-span-1">
                            <div class="flex justify-between items-center border-b pb-2 mb-4">
                                <h4 class="text-lg font-medium text-gray-800">Informasi Kepegawaian</h4>
                                <a href="{{ route('users.history', $user) }}" class="text-xs font-medium text-indigo-600 hover:text-indigo-800">
                                    Lihat Riwayat <i class="fas fa-arrow-right ml-1"></i>
                                </a>
                            </div>
                            <dl class="space-y-2">
                                <div class="flex justify-between">
                                    <dt class="font-semibold text-sm text-gray-600">Jabatan:</dt>
                                    <dd class="text-sm text-gray-900 text-right">{{ $user->jabatan->name ?? '-' }}</dd>
                                </div>
                                 <div class="flex justify-between">
                                    <dt class="font-semibold text-sm text-gray-600">Jenis Jabatan:</dt>
                                    <dd class="text-sm text-gray-900">{{ $user->jenis_jabatan ?? '-' }}</dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt class="font-semibold text-sm text-gray-600">Golongan:</dt>
                                    <dd class="text-sm text-gray-900">{{ $user->golongan ?? '-' }}</dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt class="font-semibold text-sm text-gray-600">Eselon:</dt>
                                    <dd class="text-sm text-gray-900">{{ $user->eselon ?? '-' }}</dd>
                                </div>
                                 <div class="flex justify-between">
                                    <dt class="font-semibold text-sm text-gray-600">TMT Eselon:</dt>
                                    <dd class="text-sm text-gray-900">{{ $user->tmt_eselon ? \Carbon\Carbon::parse($user->tmt_eselon)->format('d M Y') : '-' }}</dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt class="font-semibold text-sm text-gray-600">Grade:</dt>
                                    <dd class="text-sm text-gray-900">{{ $user->grade ?? '-' }}</dd>
                                </div>
                                 <div class="flex justify-between">
                                    <dt class="font-semibold text-sm text-gray-600">TMT CPNS:</dt>
                                    <dd class="text-sm text-gray-900">{{ $user->tmt_cpns ? \Carbon\Carbon::parse($user->tmt_cpns)->format('d M Y') : '-' }}</dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt class="font-semibold text-sm text-gray-600">TMT PNS:</dt>
                                    <dd class="text-sm text-gray-900">{{ $user->tmt_pns ? \Carbon\Carbon::parse($user->tmt_pns)->format('d M Y') : '-' }}</dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt class="font-semibold text-sm text-gray-600">Pendidikan:</dt>
                                    <dd class="text-sm text-gray-900 text-right">{{ $user->pendidikan_terakhir ?? '-' }} ({{ $user->pendidikan_jurusan ?? 'N/A' }})</dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt class="font-semibold text-sm text-gray-600">Universitas:</dt>
                                    <dd class="text-sm text-gray-900 text-right">{{ $user->pendidikan_universitas ?? '-' }}</dd>
                                </div>
                            </dl>
                        </div>

                        {{-- Unit Information --}}
                        <div class="md:col-span-1">
                            <h4 class="text-lg font-medium text-gray-800 border-b pb-2 mb-4">Informasi Unit & Atasan</h4>
                            <dl class="space-y-2">
                                <div class="flex justify-between">
                                    <dt class="font-semibold text-sm text-gray-600">Role:</dt>
                                    <dd class="text-sm text-gray-900"><span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">{{ $user->role }}</span></dd>
                                </div>
                                <div class="flex flex-col">
                                    <dt class="font-semibold text-sm text-gray-600 mb-1">Unit Kerja:</dt>
                                    <dd class="text-sm text-gray-900">{{ $user->unit->name ?? 'N/A' }}</dd>
                                </div>
                                 <div class="flex flex-col">
                                    <dt class="font-semibold text-sm text-gray-600 mb-1">Atasan Langsung:</dt>
                                    <dd class="text-sm text-gray-900">{{ $user->atasan->name ?? 'N/A' }}</dd>
                                    <dd class="text-xs text-gray-500">{{ $user->atasan->jabatan->name ?? '' }}</dd>
                                </div>
                            </dl>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
