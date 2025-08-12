<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Profil Pegawai: {{ $user->name }}
            </h2>
            <a href="{{ route('users.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-gray-800 uppercase tracking-widest hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150">
                <i class="fas fa-arrow-left mr-2"></i> Kembali ke Daftar
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6 sm:p-8 bg-white border-b border-gray-200">

                    {{-- Helper function to display a profile field --}}
                    @php
                    function profile_field($label, $value, $is_full_width = false) {
                        $width_class = $is_full_width ? 'md:col-span-2' : '';
                        echo "<div class='py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4 {$width_class}'>";
                        echo "<dt class='text-sm font-medium text-gray-500'>{$label}</dt>";
                        echo "<dd class='mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2'>" . ($value ?: '-') . "</dd>";
                        echo "</div>";
                    }
                    @endphp

                    {{-- Personal Information Section --}}
                    <div class="border-b border-gray-200 pb-5 mb-5">
                        <h3 class="text-lg leading-6 font-medium text-gray-900">
                            Informasi Pribadi
                        </h3>
                        <p class="mt-1 max-w-2xl text-sm text-gray-500">
                            Detail data pribadi pegawai.
                        </p>
                    </div>
                    <dl class="divide-y divide-gray-200">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8">
                            @php
                            profile_field('NIP', $user->nip);
                            profile_field('Email', $user->email);
                            profile_field('Tempat Lahir', $user->tempat_lahir);
                            profile_field('Tanggal Lahir', $user->tgl_lahir);
                            profile_field('Jenis Kelamin', $user->jenis_kelamin);
                            profile_field('Agama', $user->agama);
                            profile_field('No. HP', $user->no_hp);
                            profile_field('Telepon', $user->telepon);
                            profile_field('NPWP', $user->npwp);
                            profile_field('Pendidikan Terakhir', $user->pendidikan_terakhir);
                            profile_field('Alamat', $user->alamat, true);
                            @endphp
                        </div>
                    </dl>

                    {{-- Employment Information Section --}}
                    <div class="border-b border-gray-200 pb-5 mb-5 mt-10">
                        <h3 class="text-lg leading-6 font-medium text-gray-900">
                            Informasi Kepegawaian
                        </h3>
                        <p class="mt-1 max-w-2xl text-sm text-gray-500">
                            Detail mengenai status dan riwayat kepegawaian.
                        </p>
                    </div>
                    <dl class="divide-y divide-gray-200">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8">
                            @php
                            profile_field('Unit Kerja', $user->unit_path);
                            profile_field('Jabatan', optional($user->jabatan)->name);
                            profile_field('Jenis Jabatan', $user->jenis_jabatan);
                            profile_field('Golongan', $user->golongan);
                            profile_field('TMT Golongan', $user->tmt_gol);
                            profile_field('Eselon', $user->eselon);
                            profile_field('TMT Eselon', $user->tmt_eselon);
                            profile_field('Grade', $user->grade);
                            profile_field('TMT Jabatan', $user->tmt_jabatan);
                            profile_field('TMT CPNS', $user->tmt_cpns);
                            profile_field('TMT PNS', $user->tmt_pns);
                            profile_field('Atasan Langsung', optional($user->atasan)->name);
                            @endphp
                        </div>
                    </dl>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
