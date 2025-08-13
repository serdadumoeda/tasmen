<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('User Profile') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Informasi Pribadi</h3>
                            <div class="mt-4 space-y-4">
                                <p><strong>Nama:</strong> {{ $user->name }}</p>
                                <p><strong>NIP:</strong> {{ $user->nip ?? '-' }}</p>
                                <p><strong>Email:</strong> {{ $user->email }}</p>
                                <p><strong>Tempat, Tgl. Lahir:</strong> {{ $user->tempat_lahir ?? '-' }}, {{ $user->tgl_lahir ? \Carbon\Carbon::parse($user->tgl_lahir)->isoFormat('D MMMM YYYY') : '-' }}</p>
                                <p><strong>Jenis Kelamin:</strong> {{ $user->jenis_kelamin == 'L' ? 'Laki-laki' : 'Perempuan' }}</p>
                                <p><strong>Agama:</strong> {{ $user->agama ?? '-' }}</p>
                                <p><strong>Alamat:</strong> {{ $user->alamat ?? '-' }}</p>
                                <p><strong>Telepon:</strong> {{ $user->telepon ?? '-' }}</p>
                                <p><strong>No. HP:</strong> {{ $user->no_hp ?? '-' }}</p>
                                <p><strong>NPWP:</strong> {{ $user->npwp ?? '-' }}</p>
                            </div>
                        </div>

                        <div>
                            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Informasi Kepegawaian</h3>
                            <div class="mt-4 space-y-4">
                                <p><strong>Jabatan:</strong> {{ $user->jabatan->name ?? '-' }}</p>
                                <p><strong>Unit Kerja:</strong> {{ $user->unit->name ?? '-' }}</p>
                                <p><strong>Atasan:</strong> {{ $user->atasan->name ?? '-' }}</p>
                                <p><strong>Role:</strong> {{ $user->role }}</p>
                                <p><strong>Golongan:</strong> {{ $user->golongan ?? '-' }}</p>
                                <p><strong>Eselon:</strong> {{ $user->eselon ?? '-' }}</p>
                                <p><strong>TMT Eselon:</strong> {{ $user->tmt_eselon ? \Carbon\Carbon::parse($user->tmt_eselon)->isoFormat('D MMMM YYYY') : '-' }}</p>
                                <p><strong>Grade:</strong> {{ $user->grade ?? '-' }}</p>
                                <p><strong>Jenis Jabatan:</strong> {{ $user->jenis_jabatan ?? '-' }}</p>
                                <p><strong>TMT CPNS:</strong> {{ $user->tmt_cpns ? \Carbon\Carbon::parse($user->tmt_cpns)->isoFormat('D MMMM YYYY') : '-' }}</p>
                                <p><strong>TMT PNS:</strong> {{ $user->tmt_pns ? \Carbon\Carbon::parse($user->tmt_pns)->isoFormat('D MMMM YYYY') : '-' }}</p>
                            </div>
                        </div>

                        <div>
                            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Pendidikan Terakhir</h3>
                            <div class="mt-4 space-y-4">
                                <p><strong>Tingkat:</strong> {{ $user->pendidikan_terakhir ?? '-' }}</p>
                                <p><strong>Jurusan:</strong> {{ $user->pendidikan_jurusan ?? '-' }}</p>
                                <p><strong>Instansi:</strong> {{ $user->pendidikan_instansi ?? '-' }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="mt-8">
                        <a href="{{ route('users.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-300 dark:bg-gray-700 border border-transparent rounded-md font-semibold text-xs text-gray-800 dark:text-gray-200 uppercase tracking-widest hover:bg-gray-400 dark:hover:bg-gray-600 focus:outline-none focus:border-gray-900 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150">
                            Kembali ke Daftar Pengguna
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
