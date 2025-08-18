<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            <i class="fas fa-user-circle mr-2"></i> {{ __('Dasbor Saya') }}
        </h2>
    </x-slot>

    <div class="py-12 bg-gray-100">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- User-specific stats -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-6 mb-8">
                <div class="bg-white p-6 rounded-xl shadow-lg text-center">
                    <p class="text-3xl font-bold text-blue-600">{{ $stats['total'] }}</p>
                    <p class="text-gray-500 font-medium">Total Tugas</p>
                </div>
                <div class="bg-white p-6 rounded-xl shadow-lg text-center">
                    <p class="text-3xl font-bold text-yellow-600">{{ $stats['pending'] }}</p>
                    <p class="text-gray-500 font-medium">Menunggu</p>
                </div>
                <div class="bg-white p-6 rounded-xl shadow-lg text-center">
                    <p class="text-3xl font-bold text-orange-600">{{ $stats['in_progress'] }}</p>
                    <p class="text-gray-500 font-medium">Dikerjakan</p>
                </div>
                <div class="bg-white p-6 rounded-xl shadow-lg text-center">
                    <p class="text-3xl font-bold text-green-600">{{ $stats['completed'] }}</p>
                    <p class="text-gray-500 font-medium">Selesai</p>
                </div>
                <div class="bg-white p-6 rounded-xl shadow-lg text-center">
                    <p class="text-3xl font-bold text-red-600">{{ $stats['overdue'] }}</p>
                    <p class="text-gray-500 font-medium">Terlambat</p>
                </div>
            </div>

            <!-- Main Content Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Left Column: My Tasks -->
                <div class="lg:col-span-2 space-y-6">
                    <div class="bg-white p-6 rounded-xl shadow-lg">
                        <h3 class="text-lg font-semibold mb-4 text-gray-900">Daftar Tugas Anda</h3>
                        <div class="space-y-4">
                            @forelse ($tasks as $task)
                                <a href="{{ route('projects.show', $task->project_id) }}#task-{{$task->id}}" class="block p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition">
                                    <div class="flex justify-between items-start">
                                        <div>
                                            <p class="font-bold text-indigo-700">{{ $task->title }}</p>
                                            <p class="text-sm text-gray-500">
                                                Kegiatan: {{ $task->project->name ?? 'Tugas Harian' }}
                                            </p>
                                        </div>
                                        <span class="text-xs font-semibold px-3 py-1 rounded-full
                                            @if($task->status == 'completed') bg-green-100 text-green-800
                                            @elseif($task->status == 'in_progress') bg-blue-100 text-blue-800
                                            @elseif($task->status == 'pending') bg-yellow-100 text-yellow-800
                                            @else bg-gray-100 text-gray-800 @endif">
                                            {{ \Illuminate\Support\Str::title(str_replace('_', ' ', $task->status)) }}
                                        </span>
                                    </div>
                                    <div class="text-sm text-gray-600 mt-2">
                                        <span>Deadline: {{ \Carbon\Carbon::parse($task->deadline)->format('d M Y') }}</span>
                                    </div>
                                </a>
                            @empty
                                <p class="text-center text-gray-500 py-8">Anda tidak memiliki tugas saat ini.</p>
                            @endforelse
                        </div>
                    </div>
                </div>

                <!-- Right Column: My Activities -->
                <div class="bg-white p-6 rounded-xl shadow-lg">
                    <h3 class="text-lg font-semibold mb-4 text-gray-900">Aktivitas Terakhir Anda</h3>
                    <ul class="space-y-4">
                        @forelse($myActivities as $activity)
                            <li class="flex items-start space-x-3">
                                <div class="flex-shrink-0 pt-1">
                                    <i class="fas fa-dot-circle text-gray-400"></i>
                                </div>
                                <div class="flex-1">
                                    <p class="text-sm text-gray-700">
                                        Anda {{ $activity->description }}
                                        @if($activity->subject)
                                        <span class="font-semibold text-indigo-600">"{{ \Illuminate\Support\Str::limit(optional($activity->subject)->title ?? optional($activity->subject)->name, 25) }}"</span>
                                        @endif
                                    </p>
                                    <p class="text-xs text-gray-400 mt-0.5">{{ $activity->created_at->diffForHumans() }}</p>
                                </div>
                            </li>
                        @empty
                            <p class="text-center text-gray-500 py-8">Tidak ada aktivitas terbaru.</p>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
