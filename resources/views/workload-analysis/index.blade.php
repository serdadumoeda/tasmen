<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dasbor Kinerja & Beban Kerja Tim') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-full mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">

                    @if (session('success'))
                        <div class="mb-4 font-medium text-sm text-green-600 bg-green-100 p-3 rounded-md border border-green-200">
                            {{ session('success') }}
                        </div>
                    @endif

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 border">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Pegawai / Jabatan
                                    </th>
                                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Beban Kerja (Estimasi)
                                    </th>
                                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Hasil Kinerja (Otomatis)
                                    </th>
                                    <th scope="col" class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Predikat SKP
                                    </th>
                                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Penilaian Perilaku Kerja
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse ($subordinates as $user)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-4 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900">{{ $user->name }}</div>
                                            <div class="text-sm text-gray-500">{{ $user->role }}</div>
                                        </td>
                                        <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-600">
                                            @php
                                                $totalHours = $user->total_project_hours + $user->total_ad_hoc_hours;
                                            @endphp
                                            <ul class="list-disc list-inside">
                                                <li><strong>Total: {{ $totalHours }} Jam</strong></li>
                                                <li class="text-gray-500">Proyek: {{ $user->total_project_hours }} Jam</li>
                                                <li class="text-gray-500">Harian: {{ $user->total_ad_hoc_hours }} Jam</li>
                                                <li class="text-gray-500">SK Aktif: {{ $user->active_sk_count }}</li>
                                            </ul>
                                        </td>
                                        <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-600">
                                            <ul class="list-disc list-inside">
                                                <li>Rating Hasil: <strong>{{ $user->work_result_rating }}</strong></li>
                                                @if($user->isManager())
                                                    <li>Nilai Gabungan: <strong>{{ number_format($user->getFinalPerformanceValueAttribute(), 2) }}</strong></li>
                                                    <li><span class="text-gray-500">Individu (IHK): {{ number_format($user->individual_performance_index, 2) }}</span></li>
                                                    <li><span class="text-gray-500">Tim (SKM): {{ number_format($user->managerial_performance_score, 2) }}</span></li>
                                                @else
                                                    <li>Indeks (IHK): <strong>{{ number_format($user->individual_performance_index, 2) }}</strong></li>
                                                @endif
                                            </ul>
                                        </td>
                                        <td class="px-4 py-4 whitespace-nowrap text-center">
                                            @php
                                                $predicate = $user->performance_predicate;
                                                $colorClass = 'bg-blue-100 text-blue-800'; // Default untuk Baik
                                                if ($predicate === 'Sangat Baik') $colorClass = 'bg-green-100 text-green-800';
                                                if ($predicate === 'Butuh Perbaikan') $colorClass = 'bg-yellow-100 text-yellow-800';
                                                if ($predicate === 'Sangat Kurang') $colorClass = 'bg-red-100 text-red-800';
                                            @endphp
                                            <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full {{ $colorClass }}">
                                                {{ $predicate }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-4 whitespace-nowrap">
                                            @php
                                                $loggedInUser = Auth::user();
                                                $canRate = false;
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
                                                    <div class="flex items-center space-x-2">
                                                        <select name="work_behavior_rating" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 text-sm">
                                                            <option value="Diatas Ekspektasi" @if($user->work_behavior_rating == 'Diatas Ekspektasi') selected @endif>Diatas Ekspektasi</option>
                                                            <option value="Sesuai Ekspektasi" @if(is_null($user->work_behavior_rating) || $user->work_behavior_rating == 'Sesuai Ekspektasi') selected @endif>Sesuai Ekspektasi</option>
                                                            <option value="Dibawah Ekspektasi" @if($user->work_behavior_rating == 'Dibawah Ekspektasi') selected @endif>Dibawah Ekspektasi</option>
                                                        </select>
                                                        <button type="submit" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                                            Simpan
                                                        </button>
                                                    </div>
                                                </form>
                                            @else
                                                <div class="text-sm text-gray-500 italic">
                                                    <p>Nilai: <strong>{{ $user->work_behavior_rating ?? 'Sesuai Ekspektasi' }}</strong></p>
                                                    <p class="text-xs">(Dinilai oleh Eselon I/II)</p>
                                                </div>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-500">
                                            Anda tidak memiliki bawahan untuk ditampilkan.
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