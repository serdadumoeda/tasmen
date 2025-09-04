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
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
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
