<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Dasbor Kinerja & Beban Kerja Tim') }}
            </h2>
            <x-secondary-button :href="route('workload.analysis.workflow')">
                <i class="fas fa-sitemap mr-2"></i>
                Lihat Alur Kerja
            </x-secondary-button>
        </div>
    </x-slot>

    <div class="py-12 bg-gray-50">
        <div class="max-w-full mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">

                    @if (session('success'))
                        <div class="mb-6 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg relative shadow-md" role="alert">
                            <div class="flex items-center">
                                <i class="fas fa-check-circle mr-3 text-green-500"></i>
                                <span class="block sm:inline">{{ session('success') }}</span>
                            </div>
                        </div>
                    @endif

                    <!-- Chart Section -->
                    <div class="mb-8 p-4 border rounded-lg bg-gray-50 shadow-inner">
                        <h3 class="text-lg font-bold text-gray-800 mb-4">Perbandingan Beban Kerja Tim (Estimasi Jam @if($period !== 'all') - {{ ucfirst($period) }} @endif)</h3>
                        <div class="h-64">
                            <canvas id="workloadChart"></canvas>
                        </div>
                    </div>

                    <!-- Period Filter -->
                    <div class="mb-6">
                        <div class="flex items-center justify-center space-x-2 bg-gray-100 p-2 rounded-lg">
                            @php
                                $currentPeriod = request('period', 'all');
                                $baseClasses = 'px-4 py-2 text-sm font-semibold rounded-md transition-colors duration-200';
                                $activeClasses = 'bg-indigo-600 text-white shadow-md';
                                $inactiveClasses = 'bg-white text-gray-700 hover:bg-indigo-100 border border-gray-200';
                            @endphp
                            <a href="{{ route('workload.analysis', ['period' => 'all']) }}" class="{{ $baseClasses }} {{ $currentPeriod == 'all' ? $activeClasses : $inactiveClasses }}">Semua</a>
                            <a href="{{ route('workload.analysis', ['period' => 'weekly']) }}" class="{{ $baseClasses }} {{ $currentPeriod == 'weekly' ? $activeClasses : $inactiveClasses }}">Mingguan</a>
                            <a href="{{ route('workload.analysis', ['period' => 'monthly']) }}" class="{{ $baseClasses }} {{ $currentPeriod == 'monthly' ? $activeClasses : $inactiveClasses }}">Bulanan</a>
                            <a href="{{ route('workload.analysis', ['period' => 'quarterly']) }}" class="{{ $baseClasses }} {{ $currentPeriod == 'quarterly' ? $activeClasses : $inactiveClasses }}">Triwulanan</a>
                            <a href="{{ route('workload.analysis', ['period' => 'semester']) }}" class="{{ $baseClasses }} {{ $currentPeriod == 'semester' ? $activeClasses : $inactiveClasses }}">Semester</a>
                            <a href="{{ route('workload.analysis', ['period' => 'yearly']) }}" class="{{ $baseClasses }} {{ $currentPeriod == 'yearly' ? $activeClasses : $inactiveClasses }}">Tahunan</a>
                        </div>
                    </div>

                    <!-- Form Pencarian -->
                    <div class="mb-6">
                        <form action="{{ route('workload.analysis') }}" method="GET">
                             <input type="hidden" name="period" value="{{ $currentPeriod }}">
                            <div class="relative">
                                <input type="text" name="search" placeholder="Cari nama pegawai..." value="{{ $search ?? '' }}" class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-search text-gray-400"></i>
                                </div>
                            </div>
                        </form>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 border border-gray-200 rounded-lg">
                            <thead class="bg-gray-100">
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
                            <tbody class="bg-white divide-y divide-gray-100">
                                @forelse ($subordinates as $user)
                                    <tr id="user-row-{{ $user->id }}" class="hover:bg-gray-50 transition-colors duration-150">
                                        <td class="px-4 py-4 whitespace-nowrap">
                                            <a href="{{ route('workload.analysis.show', $user) }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-900 hover:underline">
                                                <div class="flex items-center"><i class="fas fa-user mr-2 text-gray-500"></i> {{ $user->name }}</div>
                                            </a>
                                            <div class="text-xs text-gray-500 ml-5">{{ $user->jabatan->name ?? 'N/A' }}</div>
                                        </td>
                                        <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-700">
                                            @php
                                                $userData = $workloadData[$user->id];
                                                $activeSkCount = $userData['active_sk_count'];
                                            @endphp

                                            @if($period !== 'all')
                                                {{-- Tampilan Periodik --}}
                                                @php
                                                    $totalHours = $userData['total_hours'];
                                                    $effectiveHours = $userData['effective_hours'];
                                                    $percentage = $userData['percentage'];
                                                    $color = 'text-green-500';
                                                    if ($percentage > 75) $color = 'text-yellow-500';
                                                    if ($percentage > 100) $color = 'text-red-500';
                                                @endphp
                                                <div class="flex items-center mb-2">
                                                    <i class="fas fa-tachometer-alt mr-2 {{ $color }}"></i>
                                                    <strong class="text-base {{ str_replace('text-', 'text-', $color) }}">{{ round($percentage) }}%</strong>
                                                </div>
                                                <div class="w-full bg-gray-200 rounded-full h-2.5 shadow-inner">
                                                    <div class="{{ str_replace('text-', 'bg-', $color) }} h-2.5 rounded-full" style="width: {{ min(100, $percentage) }}%"></div>
                                                </div>
                                                <ul class="space-y-1 text-xs mt-2">
                                                    <li class="flex items-center justify-between">
                                                        <span><i class="fas fa-tasks mr-2 text-gray-400"></i>Jam Tugas</span>
                                                        <strong>{{ $totalHours }} Jam</strong>
                                                    </li>
                                                    <li class="flex items-center justify-between">
                                                        <span><i class="fas fa-calendar-check mr-2 text-gray-400"></i>Jam Efektif</span>
                                                        <strong>{{ $effectiveHours }} Jam</strong>
                                                    </li>
                                                    <li class="flex items-center justify-between pt-1 mt-1 border-t">
                                                        <span><i class="fas fa-file-signature mr-2 text-gray-500"></i>SK Aktif</span>
                                                        <strong>{{ $activeSkCount }}</strong>
                                                    </li>
                                                </ul>
                                            @else
                                                {{-- Tampilan "Semua" / Fallback --}}
                                                @php
                                                    $internalHours = $workloadData[$user->id]['internal_hours'];
                                                    $externalHours = $workloadData[$user->id]['external_hours'];
                                                    $totalAllTimeHours = $internalHours + $externalHours;
                                                    $internalPercent = $totalAllTimeHours > 0 ? ($internalHours / $totalAllTimeHours) * 100 : 0;
                                                    $externalPercent = $totalAllTimeHours > 0 ? ($externalHours / $totalAllTimeHours) * 100 : 0;
                                                @endphp
                                                <div class="flex items-center mb-2">
                                                    <i class="fas fa-hourglass-start mr-2 text-blue-500"></i>
                                                    <strong class="text-base">Total: {{ $totalAllTimeHours }} Jam</strong>
                                                </div>
                                                <div class="w-full bg-gray-200 rounded-full h-4 mb-2 overflow-hidden shadow-inner">
                                                    <div class="bg-blue-600 h-4 text-xs font-medium text-blue-100 text-center p-0.5 leading-none" style="width: {{ $internalPercent }}%" title="Tugas Dalam Unit ({{ round($internalPercent) }}%)"></div>
                                                    <div class="bg-yellow-500 h-4 text-xs font-medium text-yellow-100 text-center p-0.5 leading-none" style="width: {{ $externalPercent }}%" title="Tugas Luar Unit ({{ round($externalPercent) }}%)"></div>
                                                </div>
                                                <ul class="space-y-1 text-xs">
                                                    <li class="flex items-center justify-between">
                                                        <span><i class="fas fa-building-user mr-2 text-blue-600"></i>Tugas Dalam Unit</span>
                                                        <strong>{{ $internalHours }} Jam ({{ round($internalPercent) }}%)</strong>
                                                    </li>
                                                    <li class="flex items-center justify-between">
                                                        <span><i class="fas fa-people-arrows mr-2 text-yellow-500"></i>Tugas Luar Unit (Bantuan)</span>
                                                        <strong>{{ $externalHours }} Jam ({{ round($externalPercent) }}%)</strong>
                                                    </li>
                                                    <li class="flex items-center justify-between pt-1 mt-1 border-t">
                                                        <span><i class="fas fa-file-signature mr-2 text-gray-500"></i>SK Aktif</span>
                                                        <strong>{{ $activeSkCount }}</strong>
                                                    </li>
                                                </ul>
                                            @endif
                                        </td>
                                        <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-700">
                                            <ul class="space-y-1">
                                                <li class="flex items-center"><i class="fas fa-star mr-2 text-yellow-500"></i> Rating Hasil: <strong id="work-result-{{ $user->id }}">{{ $user->work_result_rating }}</strong></li>
                                                @if($user->isManager())
                                                    <li class="flex items-center"><i class="fas fa-sitemap mr-2 text-purple-500"></i> Nilai Gabungan: <strong id="nkf-{{ $user->id }}">{{ number_format($user->final_performance_value, 2) }}</strong></li>
                                                    <li class="flex items-center text-gray-600"><i class="fas fa-user-check mr-2 text-gray-400"></i> Individu (IHK): <span id="iki-{{ $user->id }}">{{ number_format($user->individual_performance_index, 2) }}</span></li>
                                                @else
                                                    <li class="flex items-center"><i class="fas fa-chart-line mr-2 text-green-500"></i> Indeks (IHK): <strong id="iki-{{ $user->id }}">{{ number_format($user->individual_performance_index, 2) }}</strong></li>
                                                @endif
                                            </ul>
                                        </td>
                                        <td class="px-4 py-4 whitespace-nowrap text-center">
                                            <span id="predicate-wrapper-{{ $user->id }}">
                                                @php
                                                    $predicate = $user->performance_predicate;
                                                    $colorClass = 'bg-blue-200 text-blue-900';
                                                    if ($predicate === 'Sangat Baik') $colorClass = 'bg-green-200 text-green-900';
                                                    if ($predicate === 'Butuh Perbaikan') $colorClass = 'bg-yellow-200 text-yellow-900';
                                                    if ($predicate === 'Sangat Kurang') $colorClass = 'bg-red-200 text-red-900';
                                                @endphp
                                                <span id="predicate-{{ $user->id }}" class="px-3 py-1 inline-flex text-xs leading-5 font-bold rounded-full {{ $colorClass }} shadow-sm">
                                                    {{ $predicate }}
                                                </span>
                                            </span>
                                        </td>
                                        <td class="px-4 py-4 whitespace-nowrap">
                                            @can('rateBehavior', $user)
                                                <form id="form-rate-{{ $user->id }}" class="form-rating" action="{{ route('workload.updateBehavior', $user) }}" method="POST">
                                                    @csrf
                                                    @method('PATCH')
                                                    <div class="flex flex-col space-y-2">
                                                        <select name="work_behavior_rating" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 transition duration-150 text-sm">
                                                            <option value="Diatas Ekspektasi" @if($user->work_behavior_rating == 'Diatas Ekspektasi') selected @endif>Diatas Ekspektasi</option>
                                                            <option value="Sesuai Ekspektasi" @if(is_null($user->work_behavior_rating) || $user->work_behavior_rating == 'Sesuai Ekspektasi') selected @endif>Sesuai Ekspektasi</option>
                                                            <option value="Dibawah Ekspektasi" @if($user->work_behavior_rating == 'Dibawah Ekspektasi') selected @endif>Dibawah Ekspektasi</option>
                                                        </select>
                                                        <button type="submit" class="btn-submit-rating inline-flex justify-center items-center py-2 px-4 border border-transparent shadow-md text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700">
                                                            <i class="fas fa-save mr-2"></i> Simpan
                                                        </button>
                                                    </div>
                                                </form>
                                            @else
                                                <div class="text-sm text-gray-600 italic p-2 bg-gray-50 rounded-lg shadow-inner">
                                                    <p class="flex items-center"><i class="fas fa-star-half-stroke mr-2 text-gray-500"></i> Nilai: <strong>{{ $user->work_behavior_rating ?? 'Sesuai Ekspektasi' }}</strong></p>
                                                    <p class="text-xs mt-1 text-gray-500">(Dinilai oleh atasan)</p>
                                                </div>
                                            @endcan
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-6 py-8 whitespace-nowrap text-center text-lg text-gray-500 bg-gray-50 rounded-lg shadow-md">
                                            <p>Tidak ada bawahan yang cocok dengan pencarian Anda.</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-8">
                        {{ $subordinates->links() }}
                    </div>

                </div>
            </div>
        </div>
    </div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const workloadCtx = document.getElementById('workloadChart');
        if (workloadCtx) {
            const chartData = @json($chartData);
            new Chart(workloadCtx, {
                type: 'bar',
                data: {
                    labels: Object.keys(chartData),
                    datasets: [{
                        label: 'Total Estimasi Jam',
                        data: Object.values(chartData),
                        backgroundColor: 'rgba(59, 130, 246, 0.5)',
                        borderColor: 'rgba(59, 130, 246, 1)',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: 'Estimasi Jam Kerja'
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            display: false
                        }
                    }
                }
            });
        }
    });
</script>
<style>
    .btn-submit-rating.loading {
        position: relative;
        color: transparent;
        cursor: wait;
    }
    .btn-submit-rating.loading::after {
        content: '';
        position: absolute;
        left: 50%;
        top: 50%;
        margin-left: -12px;
        margin-top: -12px;
        width: 24px;
        height: 24px;
        border: 2px solid #fff;
        border-top-color: transparent;
        border-radius: 50%;
        animation: spin 0.8s linear infinite;
    }
    @keyframes spin {
        to { transform: rotate(360deg); }
    }
</style>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const forms = document.querySelectorAll('.form-rating');

    forms.forEach(form => {
        form.addEventListener('submit', function (event) {
            event.preventDefault();

            const button = form.querySelector('.btn-submit-rating');
            button.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Menyimpan...';
            button.disabled = true;

            const action = form.getAttribute('action');
            const method = form.querySelector('input[name=\"_method\"]').value || 'POST';
            const csrfToken = form.querySelector('input[name=\"_token\"]').value;
            const ratingSelect = form.querySelector('select[name=\"work_behavior_rating\"]');
            const bodyData = {
                work_behavior_rating: ratingSelect.value
            };

            fetch(action, {
                method: method,
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(bodyData)
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    updateRow(data.user);
                    // No need for separate notification function if we use a global event bus or similar
                }
            })
            .catch(error => {
                console.error('Error:', error);
            })
            .finally(() => {
                button.innerHTML = '<i class="fas fa-save mr-2"></i> Simpan';
                button.disabled = false;
            });
        });
    });

    function updateRow(userData) {
        const userId = userData.id;
        const predicateSpan = document.getElementById(`predicate-${userId}`);
        if (predicateSpan) {
            predicateSpan.textContent = userData.performance_predicate;
            let colorClass = 'bg-blue-200 text-blue-900';
            if (userData.performance_predicate === 'Sangat Baik') colorClass = 'bg-green-200 text-green-900';
            if (userData.performance_predicate === 'Butuh Perbaikan') colorClass = 'bg-yellow-200 text-yellow-900';
            if (userData.performance_predicate === 'Sangat Kurang') colorClass = 'bg-red-200 text-red-900';
            predicateSpan.className = `px-3 py-1 inline-flex text-xs leading-5 font-bold rounded-full ${colorClass} shadow-sm`;
        }
        const workResultSpan = document.getElementById(`work-result-${userId}`);
        if (workResultSpan) workResultSpan.textContent = userData.work_result_rating;
        const nkfSpan = document.getElementById(`nkf-${userId}`);
        if (nkfSpan) nkfSpan.textContent = parseFloat(userData.final_performance_value).toFixed(2);
        const ikiSpan = document.getElementById(`iki-${userId}`);
        if (ikiSpan) ikiSpan.textContent = parseFloat(userData.individual_performance_index).toFixed(2);
    }
});
</script>
@endpush
</x-app-layout>