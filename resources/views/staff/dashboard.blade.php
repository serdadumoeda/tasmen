<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard Staff') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 grid grid-cols-1 lg:grid-cols-2 gap-8">

            <!-- My Tasks Section -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-medium text-gray-900 border-b pb-2 mb-4">Tugas Aktif Saya</h3>
                    @forelse ($tasks as $task)
                        <div class="mb-3 p-2 rounded-md hover:bg-gray-50">
                            <p class="font-semibold">{{ $task->name }}</p>
                            @if ($task->project)
                                <p class="text-sm text-gray-500">Proyek: {{ $task->project->name }}</p>
                            @endif
                            <p class="text-xs text-gray-400 mt-1">Due: {{ $task->due_date ? \Carbon\Carbon::parse($task->due_date)->format('d M Y') : 'N/A' }}</p>
                        </div>
                    @empty
                        <p class="text-sm text-gray-500">Tidak ada tugas aktif saat ini.</p>
                    @endforelse
                </div>
            </div>

            <!-- My Recent Activities Section -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-medium text-gray-900 border-b pb-2 mb-4">Aktivitas Terbaru Saya</h3>
                    <ul class="space-y-4">
                        @forelse ($activities as $activity)
                            <li class="flex items-start text-sm">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-history text-gray-400 mt-1"></i>
                                </div>
                                <div class="ml-3">
                                    <p class="text-gray-700">{{ Str::ucfirst(str_replace('_', ' ', $activity->description)) }}</p>
                                    <span class="text-xs text-gray-400">{{ $activity->created_at->diffForHumans() }}</span>
                                </div>
                            </li>
                        @empty
                            <p class="text-sm text-gray-500">Tidak ada aktivitas terbaru.</p>
                        @endforelse
                    </ul>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
