<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Analisis Beban Kerja (Man-Hours & SK)') }}
        </h2>
    </x-slot>

    <div class="py-12 bg-gray-50"> {{-- Latar belakang konsisten --}}
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg"> {{-- Shadow dan rounded-lg konsisten --}}
                <div class="p-6 text-gray-900">
                    <h3 class="text-xl font-bold text-gray-800 mb-3 flex items-center">
                        <i class="fas fa-sitemap mr-2 text-indigo-600"></i> Beban Kerja Hirarkis
                    </h3>
                    <p class="text-base text-gray-700 mb-6 flex items-center"> {{-- Menyesuaikan ukuran teks dan ikon --}}
                        <i class="fas fa-info-circle mr-3 text-blue-500 fa-lg"></i>
                        Analisis ini menggabungkan total jam dari tugas proyek aktif dengan jumlah penugasan non-proyek (SK) yang sedang berjalan. Utilisasi mingguan dihitung berdasarkan perbandingan alokasi jam kerja proyek terhadap kapasitas standar 40 jam/minggu.
                    </p>

                    <div class="overflow-x-auto border border-gray-200 rounded-lg shadow-sm"> {{-- Border pada tabel, rounded-lg, shadow-sm --}}
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-100"> {{-- Header tabel lebih menonjol --}}
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider rounded-tl-lg">
                                        <i class="fas fa-user-friends mr-2"></i> Personil
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                        <i class="fas fa-tasks mr-2"></i> Tugas Aktif
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                        <i class="fas fa-chart-line mr-2"></i> Utilisasi Proyek (Jam)
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                        <i class="fas fa-file-signature mr-2"></i> Beban SK Aktif
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider rounded-tr-lg">
                                        <i class="fas fa-weight-hanging mr-2"></i> Status Beban
                                    </th>
                                </tr>
                            </thead>
                            
                            {{-- Kita tidak lagi menggunakan <tbody> di sini, karena akan dibuat di dalam partial --}}
                            @forelse ($topLevelUsers as $user)
                                {{-- Setiap user level atas akan memulai rantai rekursifnya sendiri --}}
                                @include('workload-analysis._workload-row', ['user' => $user, 'level' => 0])
                            @empty
                                <tbody>
                                    <tr>
                                        <td colspan="5" class="px-6 py-8 text-center text-gray-500 text-lg bg-gray-50 rounded-lg shadow-md">
                                            <i class="fas fa-users-slash fa-3x text-gray-400 mb-4"></i>
                                            <p>Tidak ada data untuk dianalisis.</p>
                                            <p class="text-sm text-gray-400 mt-2">Pastikan struktur hierarki dan tugas telah diatur.</p>
                                        </td>
                                    </tr>
                                </tbody>
                            @endforelse
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>