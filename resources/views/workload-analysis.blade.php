<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Analisis Beban Kerja (Man-Hours & SK)') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-medium text-gray-900 mb-2">Beban Kerja Hirarkis</h3>
                    <p class="text-sm text-gray-600 mb-6">Analisis ini menggabungkan total jam dari tugas proyek aktif dengan jumlah penugasan non-proyek (SK) yang sedang berjalan. Utilisasi mingguan dihitung berdasarkan perbandingan alokasi jam kerja proyek terhadap kapasitas standar 40 jam/minggu.</p>

                    <div class="overflow-x-auto border border-gray-200 rounded-lg">
                        <table class="min-w-full">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-1/3">Personil</th>
                                    <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider w-[12%]">Tugas Proyek</th>
                                    <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider w-1/4">Utilisasi Proyek (Jam)</th>
                                    <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider w-[12%]">Beban SK Aktif</th>
                                    <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider w-[15%]">Status Beban</th>
                                </tr>
                            </thead>
                            
                            {{-- Kita tidak lagi menggunakan <tbody> di sini, karena akan dibuat di dalam partial --}}
                            @forelse ($topLevelUsers as $user)
                                {{-- Setiap user level atas akan memulai rantai rekursifnya sendiri --}}
                                @include('workload-analysis._workload-row', ['user' => $user, 'level' => 0])
                            @empty
                                <tbody>
                                    <tr>
                                        <td colspan="5" class="px-6 py-4 text-center text-gray-500">
                                            Tidak ada data untuk dianalisis.
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