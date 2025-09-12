<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Detail Beban Kerja: ') . $user->name }}
            </h2>
            <a href="{{ route('workload.analysis') }}" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-900 focus:outline-none focus:border-gray-900 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150">
                <i class="fas fa-arrow-left mr-2"></i>
                {{ __('Kembali ke Analisis') }}
            </a>
        </div>
    </x-slot>

    <div class="py-12 bg-gray-50">
        <div class="max-w-screen-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
                    <div class="bg-blue-50 p-4 rounded-lg shadow">
                        <h3 class="text-lg font-semibold text-blue-800">Total Jam Kegiatan</h3>
                        <p class="text-3xl font-bold text-blue-900">{{ $user->total_project_hours }} Jam</p>
                    </div>
                    <div class="bg-green-50 p-4 rounded-lg shadow">
                        <h3 class="text-lg font-semibold text-green-800">Total Jam Harian</h3>
                        <p class="text-3xl font-bold text-green-900">{{ $user->total_ad_hoc_hours }} Jam</p>
                    </div>
                    <div class="bg-purple-50 p-4 rounded-lg shadow">
                        <h3 class="text-lg font-semibold text-purple-800">SK Aktif</h3>
                        <p class="text-3xl font-bold text-purple-900">{{ $user->active_sk_count }}</p>
                    </div>
                    <!-- Performance Predicate Explanation Card -->
                    <div class="bg-yellow-50 p-4 rounded-lg shadow lg:col-span-4">
                        <h3 class="text-lg font-semibold text-yellow-800 mb-2">Rincian Perhitungan Predikat Kinerja (SKP): <span class="text-yellow-900 font-bold">{{ $user->performance_predicate }}</span></h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">

                            <!-- Kolom Rating Hasil Kerja & IKI -->
                            <div class="p-4 bg-white rounded-lg border">
                                <h4 class="font-bold text-gray-800 mb-2">1. Perhitungan Indeks Kinerja Individu (IKI)</h4>
                                <p class="text-xs text-gray-600 mb-2">IKI mengukur kinerja individu berdasarkan progres tugas dan efisiensi waktu. <strong class="text-gray-800">Base Score</strong> adalah rata-rata progres semua tugas yang dibobot berdasarkan prioritasnya.</p>
                                @if(empty($performanceDetails['iki_components']))
                                    <div class="text-xs p-2 rounded bg-yellow-100 border border-yellow-300 text-yellow-800">
                                        <p><i class="fas fa-info-circle mr-1"></i> {{ $performanceDetails['iki_calculation_error'] }}</p>
                                    </div>
                                @else
                                    @php
                                        $labelMap = [
                                            'base_score' => 'Nilai Dasar',
                                            'efficiency_factor' => 'Faktor Efisiensi',
                                            'capped_efficiency_factor' => 'Faktor Efisiensi (dibatasi)',
                                        ];
                                        $definitionMap = [
                                            'base_score' => 'Rata-rata progres semua tugas Anda, dibobot berdasarkan prioritasnya.',
                                            'efficiency_factor' => 'Perbandingan antara total jam estimasi dan total jam kerja aktual Anda.',
                                            'capped_efficiency_factor' => 'Faktor Efisiensi yang nilainya telah disesuaikan agar berada dalam rentang yang wajar untuk keadilan.',
                                        ];

                                        $formatLabel = function($key, $withParentheses = false) use ($labelMap) {
                                            $label = $labelMap[$key] ?? ucwords(str_replace('_', ' ', $key));
                                            return $withParentheses ? '(' . $label . ')' : $label;
                                        };

                                        $iki_human_labels = collect($performanceDetails['iki_components'])->mapWithKeys(function ($value, $key) use ($formatLabel) {
                                            return [$key => $formatLabel($key, true)];
                                        })->all();
                                        $human_iki_formula = str_replace(array_keys($iki_human_labels), array_values($iki_human_labels), $performanceDetails['iki_formula']);
                                    @endphp
                                    @php
                                        // --- Robust replacement for the formula string ---
                                        $human_iki_formula_str = $performanceDetails['iki_formula'];
                                        $iki_human_labels_sorted = collect($iki_human_labels)->sortBy(fn($val, $key) => strlen($key) * -1);
                                        foreach ($iki_human_labels_sorted as $key => $label) {
                                            $human_iki_formula_str = str_replace($key, $label, $human_iki_formula_str);
                                        }

                                        // --- Robust replacement for the result string ---
                                        $hasil_string = $performanceDetails['iki_formula'];
                                        $components_sorted = collect($performanceDetails['iki_components'])->sortBy(fn($val, $key) => strlen($key) * -1);
                                        foreach ($components_sorted as $key => $value) {
                                            $hasil_string = str_replace($key, $value, $hasil_string);
                                        }
                                    @endphp
                                    <div class="text-xs p-2 rounded bg-gray-100 border font-mono mb-2">
                                        <p class="font-semibold">Rumus: <code class="text-red-600">{{ $human_iki_formula_str }}</code></p>
                                        <p class="font-semibold">Hasil: <code class="font-mono">{{ $hasil_string }} = <strong class="text-red-600">{{ $performanceDetails['iki_result'] }}</strong></code></p>
                                    </div>
                                    <hr class="my-2 border-gray-200">
                                    <ul class="text-xs space-y-2">
                                        @foreach($performanceDetails['iki_components'] as $key => $value)
                                        <li>
                                            <span class="font-semibold">{{ $formatLabel($key) }}:</span> {{ $value }}
                                            <p class="text-gray-500 italic pl-2"><small>{{ $definitionMap[$key] ?? '' }}</small></p>

                                            @if($key === 'base_score')
                                                @php
                                                    $getPriorityWeight = function($priority) {
                                                        return match (strtolower($priority)) {
                                                            'critical' => 4, 'high' => 3, 'medium' => 2, 'low' => 1, default => 2,
                                                        };
                                                    };
                                                    $totalWeightedScore = 0;
                                                    $totalWeight = 0;
                                                @endphp
                                                <div class="mt-2 pl-2 space-y-2">
                                                    <div class="text-gray-600 p-2 rounded bg-gray-50 border border-gray-200">
                                                        <p class="font-semibold">Legenda Bobot Prioritas:</p>
                                                        <p>Critical: 4, High: 3, Medium: 2, Low: 1</p>
                                                    </div>
                                                    <details>
                                                        <summary class="cursor-pointer text-blue-600 font-semibold select-none">[+ Lihat Rincian Perhitungan]</summary>
                                                        <div class="mt-2 border border-gray-200 rounded-md p-2">
                                                            <table class="min-w-full divide-y divide-gray-200">
                                                                <thead class="bg-gray-50">
                                                                    <tr>
                                                                        <th class="px-2 py-1 text-left font-medium text-gray-500">Tugas</th>
                                                                        <th class="px-2 py-1 text-center font-medium text-gray-500">Progres</th>
                                                                        <th class="px-2 py-1 text-center font-medium text-gray-500">Bobot</th>
                                                                        <th class="px-2 py-1 text-center font-medium text-gray-500">Skor Tertimbang</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody class="bg-white divide-y divide-gray-200">
                                                                    @forelse($user->tasks as $task)
                                                                        @php
                                                                            $priorityName = $task->priorityLevel->name ?? 'medium';
                                                                            $weight = $getPriorityWeight($priorityName);
                                                                            $weightedScore = ($task->progress / 100) * $weight;
                                                                            $totalWeightedScore += $weightedScore;
                                                                            $totalWeight += $weight;
                                                                        @endphp
                                                                        <tr>
                                                                            <td class="px-2 py-1 w-1/2">{{ $task->title }}</td>
                                                                            <td class="px-2 py-1 text-center">{{ $task->progress }}%</td>
                                                                            <td class="px-2 py-1 text-center">{{ $weight }}</td>
                                                                            <td class="px-2 py-1 text-center">{{ number_format($weightedScore, 2) }}</td>
                                                                        </tr>
                                                                    @empty
                                                                        <tr>
                                                                            <td colspan="4" class="px-2 py-2 text-center text-gray-500">Tidak ada tugas untuk dinilai.</td>
                                                                        </tr>
                                                                    @endforelse
                                                                </tbody>
                                                                @if($user->tasks->isNotEmpty())
                                                                <tfoot class="bg-gray-50 font-bold">
                                                                    <tr>
                                                                        <td colspan="2" class="px-2 py-1 text-right">Total:</td>
                                                                        <td class="px-2 py-1 text-center">{{ $totalWeight }}</td>
                                                                        <td class="px-2 py-1 text-center">{{ number_format($totalWeightedScore, 2) }}</td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td colspan="3" class="px-2 py-1 text-right">Nilai Dasar (Total Skor / Total Bobot):</td>
                                                                        <td class="px-2 py-1 text-center">{{ $totalWeight > 0 ? number_format($totalWeightedScore / $totalWeight, 3) : 0 }}</td>
                                                                    </tr>
                                                                </tfoot>
                                                                @endif
                                                            </table>
                                                        </div>
                                                    </details>
                                                </div>
                                            @endif

                                            @if($key === 'capped_efficiency_factor')
                                                @php
                                                    $original_value = $performanceDetails['iki_components']['efficiency_factor'];
                                                    $min_limit = $settings['min_efficiency_factor'] ?? 0.9;
                                                    $max_limit = $settings['max_efficiency_factor'] ?? 1.25;
                                                    $logic_text = '';
                                                    if ($original_value > $max_limit) {
                                                        $logic_text = "Karena Nilai Asli (".number_format($original_value, 3).") lebih tinggi dari Batas Atas (".number_format($max_limit, 2)."), maka nilai yang digunakan adalah ".number_format($max_limit, 2).".";
                                                    } elseif ($original_value < $min_limit) {
                                                        $logic_text = "Karena Nilai Asli (".number_format($original_value, 3).") lebih rendah dari Batas Bawah (".number_format($min_limit, 2)."), maka nilai yang digunakan adalah ".number_format($min_limit, 2).".";
                                                    } else {
                                                        $logic_text = "Karena Nilai Asli (".number_format($original_value, 3).") berada dalam rentang wajar, maka nilai asli yang digunakan.";
                                                    }
                                                @endphp
                                                <div class="mt-2 pl-2">
                                                    <details>
                                                        <summary class="cursor-pointer text-blue-600 font-semibold select-none">[+ Lihat Proses Pembatasan (Capping)]</summary>
                                                        <div class="mt-2 border border-gray-200 rounded-md p-2 space-y-1">
                                                            <p><strong>Nilai Faktor Efisiensi (Asli):</strong> {{ number_format($original_value, 3) }}</p>
                                                            <p><strong>Batas Bawah Sistem:</strong> {{ number_format($min_limit, 2) }}</p>
                                                            <p><strong>Batas Atas Sistem:</strong> {{ number_format($max_limit, 2) }}</p>
                                                            <hr class="my-1">
                                                            <p><strong>Logika:</strong> {{ $logic_text }}</p>
                                                        </div>
                                                    </details>
                                                </div>
                                            @endif

                                            @if($key === 'efficiency_factor')
                                                @php
                                                    $totalEstimatedHours = $user->tasks->sum('estimated_hours');
                                                    $timeLogs = \App\Models\TimeLog::whereIn('task_id', $user->tasks->pluck('id'))->where('user_id', $user->id)->whereNotNull('end_time')->get();
                                                    $totalActualHours = $timeLogs->sum('duration_in_minutes') / 60;
                                                @endphp
                                                <div class="mt-2 pl-2">
                                                    <details>
                                                        <summary class="cursor-pointer text-blue-600 font-semibold select-none">[+ Lihat Rincian Perhitungan]</summary>
                                                        <div class="mt-2 border border-gray-200 rounded-md p-2 space-y-2">
                                                            <div>
                                                                <p><strong>Total Jam Estimasi:</strong> {{ number_format($totalEstimatedHours, 2) }} jam</p>
                                                                <p class="text-gray-500 italic text-xs pl-2">Jumlah semua 'estimasi jam' dari setiap tugas.</p>
                                                            </div>
                                                            <div>
                                                                <p><strong>Total Jam Kerja Aktual:</strong> {{ number_format($totalActualHours, 2) }} jam</p>
                                                                <p class="text-gray-500 italic text-xs pl-2">Jumlah total waktu yang tercatat di 'Time Log' untuk semua tugas.</p>
                                                            </div>
                                                            <hr>
                                                            @if($totalActualHours > 0)
                                                                <div class="font-bold">
                                                                    <p>Perhitungan Akhir:</p>
                                                                    <p class="font-mono">{{ number_format($totalEstimatedHours, 2) }} / {{ number_format($totalActualHours, 2) }} = {{ number_format($totalEstimatedHours / $totalActualHours, 3) }}</p>
                                                                </div>
                                                            @else
                                                                <div class="mt-2 p-2 bg-orange-100 border border-orange-300 rounded text-xs">
                                                                    <p class="font-bold text-orange-800">Penjelasan Nilai Default:</p>
                                                                    <p class="text-orange-700">Karena Total Jam Kerja Aktual adalah 0, perhitungan tidak dapat dilakukan. Sistem secara otomatis memberikan nilai default **1.0** (Sesuai Ekspektasi) untuk Faktor Efisiensi.</p>
                                                                </div>
                                                            @endif
                                                        </div>
                                                    </details>
                                                </div>
                                            @endif
                                        </li>
                                        @endforeach
                                    </ul>
                                @endif
                                <div class="mt-3 pt-3 border-t text-xs text-gray-600">
                                    <p class="font-bold mb-1">Interpretasi IKI:</p>
                                    <ul class="list-disc list-inside">
                                        <li><strong class="text-gray-800">IKI > 1.0:</strong> Sangat efisien (progres lebih tinggi dari usaha).</li>
                                        <li><strong class="text-gray-800">IKI ≈ 1.0:</strong> Sesuai ekspektasi (progres sebanding dengan usaha).</li>
                                        <li><strong class="text-gray-800">IKI < 1.0:</strong> Kurang efisien (progres lebih rendah dari usaha).</li>
                                    </ul>
                                </div>
                            </div>

                            <!-- Kolom NKF & Predikat -->
                            <div class="p-4 bg-white rounded-lg border">
                                <h4 class="font-bold text-gray-800 mb-2">2. Perhitungan Nilai Kinerja Final (NKF)</h4>
                                <p class="text-xs text-gray-600 mb-2">NKF adalah skor akhir yang menjadi dasar rating hasil kerja. Untuk pimpinan, nilai ini juga dipengaruhi oleh kinerja timnya.</p>
                                @if(empty($performanceDetails['nkf_components']))
                                    <div class="text-xs p-2 rounded bg-yellow-100 border border-yellow-300 text-yellow-800">
                                        <p><i class="fas fa-info-circle mr-1"></i> {{ $performanceDetails['nkf_calculation_error'] }}</p>
                                    </div>
                                @else
                                    @php
                                        $labelMap = [
                                            'individual_score' => 'IKI Individu',
                                            'managerial_score' => 'Rata-rata Kinerja Tim',
                                            'weight' => 'Bobot Manajerial'
                                        ];
                                        $definitionMap = [
                                            'individual_score' => 'Skor IKI yang menjadi dasar perhitungan NKF.',
                                            'managerial_score' => 'Rata-rata nilai kinerja dari tim yang Anda pimpin.',
                                            'weight' => 'Bobot pengaruh antara kinerja individu dan kinerja tim.'
                                        ];

                                        $formatLabel = function($key, $withParentheses = false) use ($labelMap) {
                                            $label = $labelMap[$key] ?? ucwords(str_replace('_', ' ', $key));
                                            return $withParentheses ? '(' . $label . ')' : $label;
                                        };

                                        $nkf_human_labels = collect($performanceDetails['nkf_components'])->mapWithKeys(function ($value, $key) use ($formatLabel) {
                                            return [$key => $formatLabel($key, true)];
                                        })->all();
                                        $human_nkf_formula = str_replace(array_keys($nkf_human_labels), array_values($nkf_human_labels), $performanceDetails['nkf_formula']);
                                    @endphp
                                    @php
                                        // --- Robust replacement for the formula string ---
                                        $human_nkf_formula_str = $performanceDetails['nkf_formula'];
                                        $nkf_human_labels_sorted = collect($nkf_human_labels)->sortBy(fn($val, $key) => strlen($key) * -1);
                                        foreach ($nkf_human_labels_sorted as $key => $label) {
                                            $human_nkf_formula_str = str_replace($key, $label, $human_nkf_formula_str);
                                        }

                                        // --- Robust replacement for the result string ---
                                        $hasil_string_nkf = $performanceDetails['nkf_formula'];
                                        $components_sorted_nkf = collect($performanceDetails['nkf_components'])->sortBy(fn($val, $key) => strlen($key) * -1);
                                        foreach ($components_sorted_nkf as $key => $value) {
                                            $hasil_string_nkf = str_replace($key, $value, $hasil_string_nkf);
                                        }
                                    @endphp
                                    <div class="text-xs p-2 rounded bg-gray-100 border font-mono mb-2">
                                        <p class="font-semibold">Rumus: <code class="text-blue-600">{{ $human_nkf_formula_str }}</code></p>
                                        <p class="font-semibold">Hasil: <code class="font-mono">{{ $hasil_string_nkf }} = <strong class="text-blue-600">{{ $performanceDetails['nkf_result'] }}</strong></code></p>
                                    </div>
                                    <hr class="my-2 border-gray-200">
                                    <ul class="text-xs space-y-2 mb-3">
                                        @foreach($performanceDetails['nkf_components'] as $key => $value)
                                        <li>
                                            <span class="font-semibold">{{ $formatLabel($key) }}:</span> {{ $value }}
                                            @if(isset($definitionMap[$key]))
                                                <p class="text-gray-500 italic pl-2"><small>{{ $definitionMap[$key] }}</small></p>
                                            @endif
                                        </li>
                                        @endforeach
                                    </ul>
                                @endif
                                <div class="mt-3 pt-3 border-t text-xs text-gray-600">
                                     <p class="font-bold mb-1">Interpretasi Rating Hasil Kerja (berdasarkan NKF):</p>
                                    <ul class="list-disc list-inside">
                                        <li>Jika NKF &ge; {{ $settings['rating_threshold_high'] ?? '1.15' }} &rarr; <strong class="text-green-600">Diatas Ekspektasi</strong></li>
                                        <li>Jika NKF &ge; {{ $settings['rating_threshold_medium'] ?? '0.90' }} &rarr; <strong class="text-blue-600">Sesuai Ekspektasi</strong></li>
                                        <li>Jika NKF &lt; {{ $settings['rating_threshold_medium'] ?? '0.90' }} &rarr; <strong class="text-red-600">Dibawah Ekspektasi</strong></li>
                                    </ul>
                                </div>

                                <h4 class="font-bold text-gray-800 mb-2 pt-3 border-t">3. Penentuan Predikat</h4>
                                <p class="text-xs text-gray-600 mb-2">Predikat final adalah gabungan dari <strong>Rating Hasil Kerja</strong> (berdasarkan NKF) dan <strong>Penilaian Perilaku Kerja</strong> (dari atasan).</p>
                                <div class="text-xs p-2 rounded bg-gray-100 border text-center">
                                    <span class="font-semibold">{{ $user->work_result_rating }}</span>
                                    <span class="mx-2">+</span>
                                    <span class="font-semibold">{{ $user->work_behavior_rating ?? 'Sesuai Ekspektasi' }}</span>
                                    <span class="mx-2">&darr;</span>
                                    <strong class="text-green-600">{{ $user->performance_predicate }}</strong>
                                </div>
                                <details class="mt-2 text-xs">
                                    <summary class="cursor-pointer text-blue-600 font-semibold select-none">[+ Lihat Logika Penentuan Predikat]</summary>
                                    <div class="mt-2 p-2 border rounded-md bg-gray-50 text-gray-700">
                                        <p class="font-bold mb-1">Logika didasarkan pada matriks berikut:</p>
                                        <ul class="space-y-1 list-disc list-inside">
                                            <li><span class="font-semibold">Sangat Baik:</span> Jika 'Rating Hasil Kerja' <strong class="text-gray-900">Diatas Ekspektasi</strong> DAN 'Perilaku Kerja' <strong class="text-gray-900">Diatas Ekspektasi</strong>.</li>
                                            <li><span class="font-semibold">Baik:</span> Jika 'Rating Hasil Kerja' DAN 'Perilaku Kerja' keduanya <strong class="text-gray-900">Sesuai Ekspektasi</strong> atau lebih baik (dan tidak memenuhi syarat 'Sangat Baik').</li>
                                            <li class="font-semibold text-red-600"><span class="text-red-600">Butuh Perbaikan:</span> Jika SALAH SATU dari 'Rating Hasil Kerja' atau 'Perilaku Kerja' adalah <strong class="text-red-800">Dibawah Ekspektasi</strong>.</li>
                                            <li><span class="font-semibold">Sangat Kurang:</span> Jika 'Rating Hasil Kerja' DAN 'Perilaku Kerja' keduanya <strong class="text-gray-900">Dibawah Ekspektasi</strong>.</li>
                                        </ul>
                                    </div>
                                </details>
                            </div>

                        </div>
                    </div>
                </div>

                <!-- Tugas Harian (Ad-Hoc) -->
                <div class="mb-8">
                    <h3 class="text-2xl font-semibold text-gray-800 border-b-2 border-gray-200 pb-2 mb-4"><i class="fas fa-clipboard-list mr-3 text-green-500"></i>Tugas Harian (Ad-Hoc)</h3>
                    @if($adhocTasks->isEmpty())
                        <p class="text-gray-500 italic">Tidak ada tugas harian yang ditugaskan.</p>
                    @else
                        <div class="space-y-4">
                            @foreach($adhocTasks as $task)
                                <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
                                    <p class="font-semibold text-gray-900">{{ $task->title }}</p>
                                    <p class="text-sm text-gray-600">{{ $task->description }}</p>
                                    <div class="flex justify-between items-center mt-2 text-xs text-gray-500">
                                        <span><i class="fas fa-hourglass-half mr-1"></i> Estimasi: {{ $task->estimated_hours }} jam</span>
                                        <span class="px-2 py-1 text-white rounded-full {{ $task->status->color_class ?? 'bg-gray-400' }}">
                                            {{ $task->status->label ?? 'N/A' }}
                                        </span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                <!-- Tugas Kegiatan -->
                <div class="mb-8">
                    <h3 class="text-2xl font-semibold text-gray-800 border-b-2 border-gray-200 pb-2 mb-4"><i class="fas fa-folder-open mr-3 text-blue-500"></i>Tugas Kegiatan</h3>
                    @if($projectTasks->isEmpty())
                        <p class="text-gray-500 italic">Tidak ada tugas kegiatan yang ditugaskan.</p>
                    @else
                        <div class="space-y-6">
                            @foreach($projectTasks as $projectName => $tasks)
                                <div class="bg-white p-4 rounded-lg border border-gray-200 shadow-sm">
                                    <h4 class="text-xl font-bold text-gray-700 mb-3">{{ $projectName }}</h4>
                                    <div class="space-y-3">
                                        @foreach($tasks as $task)
                                            <div class="bg-gray-50 p-3 rounded-md">
                                                <p class="font-semibold text-gray-800">{{ $task->title }}</p>
                                                <div class="flex justify-between items-center mt-2 text-xs text-gray-500">
                                                    <span><i class="fas fa-hourglass-half mr-1"></i> Estimasi: {{ $task->estimated_hours }} jam</span>
                                                    <span class="px-2 py-1 text-white rounded-full {{ $task->status->color_class ?? 'bg-gray-400' }}">
                                                        {{ $task->status->label ?? 'N/A' }}
                                                    </span>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                <!-- Tugas Khusus (SK) -->
                <div>
                    <h3 class="text-2xl font-semibold text-gray-800 border-b-2 border-gray-200 pb-2 mb-4"><i class="fas fa-file-signature mr-3 text-purple-500"></i>Tugas Khusus (SK)</h3>
                    @if($specialAssignments->isEmpty())
                        <p class="text-gray-500 italic">Tidak ada tugas khusus (SK) yang aktif.</p>
                    @else
                        <div class="space-y-4">
                            @foreach($specialAssignments as $assignment)
                                <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
                                    <p class="font-semibold text-gray-900">{{ $assignment->title }}</p>
                                    <p class="text-sm text-gray-600">Nomor: {{ $assignment->number }}</p>
                                    <div class="flex justify-between items-center mt-2 text-xs text-gray-500">
                                        <span><i class="fas fa-calendar-alt mr-1"></i> Tanggal: {{ \Carbon\Carbon::parse($assignment->start_date)->format('d M Y') }} - {{ \Carbon\Carbon::parse($assignment->end_date)->format('d M Y') }}</span>
                                        <span class="px-2 py-1 text-white rounded-full
                                            @if($assignment->status == 'disetujui') bg-green-500
                                            @else bg-yellow-500 @endif
                                        ">{{ ucfirst($assignment->status) }}</span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
