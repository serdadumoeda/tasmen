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
                                        $formatLabel = function($key, $withParentheses = false) {
                                            $label = $key;
                                            if (str_starts_with($label, 'capped_')) {
                                                $label = str_replace('capped_', '', $label);
                                                $label = ucwords(str_replace('_', ' ', $label)) . ' (dibatasi)';
                                            } else {
                                                $label = ucwords(str_replace('_', ' ', $label));
                                            }
                                            return $withParentheses ? '(' . $label . ')' : $label;
                                        };

                                        $iki_human_labels = collect($performanceDetails['iki_components'])->mapWithKeys(function ($value, $key) use ($formatLabel) {
                                            return [$key => $formatLabel($key, true)];
                                        })->all();
                                        $human_iki_formula = str_replace(array_keys($iki_human_labels), array_values($iki_human_labels), $performanceDetails['iki_formula']);
                                    @endphp
                                    <div class="text-xs p-2 rounded bg-gray-100 border font-mono mb-2">
                                        <p class="font-semibold">Rumus: <code class="text-red-600">{{ $human_iki_formula }}</code></p>
                                        <p>Hasil: {{ str_replace(array_keys($performanceDetails['iki_components']), array_values($performanceDetails['iki_components']), $performanceDetails['iki_formula']) }} = <strong class="text-red-600">{{ $performanceDetails['iki_result'] }}</strong></p>
                                    </div>
                                    <ul class="text-xs space-y-1">
                                        @foreach($performanceDetails['iki_components'] as $key => $value)
                                        <li><span class="font-semibold">{{ $formatLabel($key) }}:</span> {{ $value }}</li>
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
                                        $formatLabel = function($key, $withParentheses = false) {
                                            $label = $key;
                                            if (str_starts_with($label, 'capped_')) {
                                                $label = str_replace('capped_', '', $label);
                                                $label = ucwords(str_replace('_', ' ', $label)) . ' (dibatasi)';
                                            } else {
                                                $label = ucwords(str_replace('_', ' ', $label));
                                            }
                                            return $withParentheses ? '(' . $label . ')' : $label;
                                        };

                                        $nkf_human_labels = collect($performanceDetails['nkf_components'])->mapWithKeys(function ($value, $key) use ($formatLabel) {
                                            return [$key => $formatLabel($key, true)];
                                        })->all();
                                        $human_nkf_formula = str_replace(array_keys($nkf_human_labels), array_values($nkf_human_labels), $performanceDetails['nkf_formula']);
                                    @endphp
                                    <div class="text-xs p-2 rounded bg-gray-100 border font-mono mb-2">
                                        <p class="font-semibold">Rumus: <code class="text-blue-600">{{ $human_nkf_formula }}</code></p>
                                        <p>Hasil: {{ str_replace(array_keys($performanceDetails['nkf_components']), array_values($performanceDetails['nkf_components']), $performanceDetails['nkf_formula']) }} = <strong class="text-blue-600">{{ $performanceDetails['nkf_result'] }}</strong></p>
                                    </div>
                                    <ul class="text-xs space-y-1 mb-3">
                                        @foreach($performanceDetails['nkf_components'] as $key => $value)
                                        <li><span class="font-semibold">{{ $formatLabel($key) }}:</span> {{ $value }}</li>
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
