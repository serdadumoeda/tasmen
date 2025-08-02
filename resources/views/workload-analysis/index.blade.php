<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dasbor Kinerja & Beban Kerja Tim') }}
        </h2>
    </x-slot>

    <div class="py-12 bg-gray-50"> {{-- Latar belakang konsisten --}}
        <div class="max-w-full mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg"> {{-- Shadow dan rounded-lg konsisten --}}
                <div class="p-6 bg-white border-b border-gray-200">

                    @if (session('success'))
                        <div class="mb-6 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg relative shadow-md" role="alert"> {{-- Styling alert konsisten --}}
                            <div class="flex items-center">
                                <i class="fas fa-check-circle mr-3 text-green-500"></i>
                                <span class="block sm:inline">{{ session('success') }}</span>
                            </div>
                        </div>
                    @endif

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 border border-gray-200 rounded-lg"> {{-- Border pada tabel, rounded-lg --}}
                            <thead class="bg-gray-100"> {{-- Header tabel lebih menonjol --}}
                                <tr>
                                    <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider rounded-tl-lg">
                                        <i class="fas fa-user-circle mr-2"></i> Pegawai / Jabatan
                                    </th>
                                    <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                        <i class="fas fa-chart-bar mr-2"></i> Beban Kerja (Estimasi)
                                    </th>
                                    <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                        <i class="fas fa-medal mr-2"></i> Hasil Kinerja (Otomatis)
                                    </th>
                                    <th scope="col" class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                        <i class="fas fa-award mr-2"></i> Predikat SKP
                                    </th>
                                    <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider rounded-tr-lg">
                                        <i class="fas fa-handshake mr-2"></i> Penilaian Perilaku Kerja
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-100"> {{-- Divider lebih halus --}}
                                @forelse ($subordinates as $user)
                                    <tr class="hover:bg-gray-50 transition-colors duration-150"> {{-- Hover effect pada baris --}}
                                        <td class="px-4 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900 flex items-center"><i class="fas fa-user mr-2 text-gray-500"></i> {{ $user->name }}</div>
                                            <div class="text-xs text-gray-500 ml-5">{{ $user->role }}</div>
                                        </td>
                                        <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-700">
                                            @php
                                                $totalHours = $user->total_project_hours + $user->total_ad_hoc_hours;
                                            @endphp
                                            <ul class="space-y-1"> {{-- Spasi antar list item --}}
                                                <li class="flex items-center"><i class="fas fa-hourglass-start mr-2 text-blue-500"></i> <strong>Total: {{ $totalHours }} Jam</strong></li>
                                                <li class="flex items-center text-gray-600"><i class="fas fa-folder-open mr-2 text-gray-400"></i> Kegiatan: {{ $user->total_project_hours }} Jam</li>
                                                <li class="flex items-center text-gray-600"><i class="fas fa-clipboard-list mr-2 text-gray-400"></i> Harian: {{ $user->total_ad_hoc_hours }} Jam</li>
                                                <li class="flex items-center text-gray-600"><i class="fas fa-file-signature mr-2 text-gray-400"></i> SK Aktif: {{ $user->active_sk_count }}</li>
                                            </ul>
                                        </td>
                                        <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-700">
                                            <ul class="space-y-1">
                                                <li class="flex items-center"><i class="fas fa-star mr-2 text-yellow-500"></i> Rating Hasil: <strong>{{ $user->work_result_rating }}</strong></li>
                                                @if($user->isManager())
                                                    <li class="flex items-center"><i class="fas fa-sitemap mr-2 text-purple-500"></i> Nilai Gabungan: <strong>{{ number_format($user->getFinalPerformanceValueAttribute(), 2) }}</strong></li>
                                                    <li class="flex items-center text-gray-600"><i class="fas fa-user-check mr-2 text-gray-400"></i> Individu (IHK): {{ number_format($user->individual_performance_index, 2) }}</li>
                                                    <li class="flex items-center text-gray-600"><i class="fas fa-users mr-2 text-gray-400"></i> Tim (SKM): {{ number_format($user->managerial_performance_score, 2) }}</li>
                                                @else
                                                    <li class="flex items-center"><i class="fas fa-chart-line mr-2 text-green-500"></i> Indeks (IHK): <strong>{{ number_format($user->individual_performance_index, 2) }}</strong></li>
                                                @endif
                                            </ul>
                                        </td>
                                        <td class="px-4 py-4 whitespace-nowrap text-center">
                                            @php
                                                $predicate = $user->performance_predicate;
                                                $colorClass = 'bg-blue-200 text-blue-900'; // Default untuk Baik
                                                if ($predicate === 'Sangat Baik') $colorClass = 'bg-green-200 text-green-900';
                                                if ($predicate === 'Butuh Perbaikan') $colorClass = 'bg-yellow-200 text-yellow-900';
                                                if ($predicate === 'Sangat Kurang') $colorClass = 'bg-red-200 text-red-900';
                                            @endphp
                                            <span class="px-3 py-1 inline-flex text-xs leading-5 font-bold rounded-full {{ $colorClass }} shadow-sm">
                                                {{ $predicate }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-4 whitespace-nowrap">
                                            @php
                                                $loggedInUser = Auth::user();
                                                $canRate = false;
                                                // Cek apakah user yang login adalah atasan langsung atau Eselon I yang menilai Eselon II
                                                if ($loggedInUser->role === 'Eselon I' && $user->role === 'Eselon II' && $user->parent_id === $loggedInUser->id) {
                                                    $canRate = true;
                                                } elseif ($loggedInUser->role === 'Eselon II' && $loggedInUser->getAllSubordinateIds()->contains($user->id)) {
                                                    $canRate = true;
                                                }
                                            @endphp

                                            @if ($canRate)
                                                <form action="{{ route('workload.updateBehavior', $user) }}" method="POST">
                                                    @csrf
                                                    @method('PATCH')
                                                    <div class="flex flex-col space-y-2"> {{-- Gunakan flex-col untuk vertikal --}}
                                                        <select name="work_behavior_rating" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 transition duration-150 text-sm">
                                                            <option value="Diatas Ekspektasi" @if($user->work_behavior_rating == 'Diatas Ekspektasi') selected @endif>Diatas Ekspektasi</option>
                                                            <option value="Sesuai Ekspektasi" @if(is_null($user->work_behavior_rating) || $user->work_behavior_rating == 'Sesuai Ekspektasi') selected @endif>Sesuai Ekspektasi</option>
                                                            <option value="Dibawah Ekspektasi" @if($user->work_behavior_rating == 'Dibawah Ekspektasi') selected @endif>Dibawah Ekspektasi</option>
                                                        </select>
                                                        <button type="submit" class="inline-flex justify-center items-center py-2 px-4 border border-transparent shadow-md text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition ease-in-out duration-150 transform hover:scale-105">
                                                            <i class="fas fa-save mr-2"></i> Simpan
                                                        </button>
                                                    </div>
                                                </form>
                                            @else
                                                <div class="text-sm text-gray-600 italic p-2 bg-gray-50 rounded-lg shadow-inner">
                                                    <p class="flex items-center"><i class="fas fa-star-half-stroke mr-2 text-gray-500"></i> Nilai: <strong>{{ $user->work_behavior_rating ?? 'Sesuai Ekspektasi' }}</strong></p>
                                                    <p class="text-xs mt-1 text-gray-500">(Dinilai oleh Eselon I/II)</p>
                                                </div>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-6 py-8 whitespace-nowrap text-center text-lg text-gray-500 bg-gray-50 rounded-lg shadow-md">
                                            <i class="fas fa-users-slash fa-3x text-gray-400 mb-4"></i>
                                            <p>Anda tidak memiliki bawahan untuk ditampilkan.</p>
                                            <p class="text-sm text-gray-400 mt-2">Pastikan struktur hierarki telah diatur dengan benar.</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>