<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Tugas Harian / Non-Proyek') }}
            </h2>
            <a href="{{ route('adhoc-tasks.create') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700">
                Tambah Tugas
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline">{{ session('success') }}</span>
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="space-y-4">
                        @forelse ($assignedTasks as $task)
                            <div class="block p-4 border border-gray-200 rounded-lg">
                                <div class="flex justify-between items-start">
                                    <div>
                                        <p class="font-bold text-indigo-600">{{ $task->title }}</p>
                                        <div class="text-sm text-gray-500 mt-1">
                                            <span>Deadline: {{ \Carbon\Carbon::parse($task->deadline)->format('d M Y') }}</span>
                                            <span class="mx-1">|</span>
                                            <span>Estimasi: {{ $task->estimated_hours }} jam</span>
                                            <span class="mx-1">|</span>
                                            <span>Ditugaskan ke: 
                                                @foreach($task->assignees as $assignee)
                                                    {{ $assignee->name }}{{ !$loop->last ? ',' : '' }}
                                                @endforeach
                                            </span>
                                        </div>
                                    </div>
                                    <div class="flex items-center space-x-2 flex-shrink-0">
                                        <a href="{{ route('tasks.edit', $task) }}" class="text-indigo-600 hover:text-indigo-900 text-sm font-medium">
                                            Detail/Edit
                                        </a>
                                    </div>
                                </div>
                                <div class="mt-2">
                                    <div class="flex justify-between mb-1">
                                        <span class="text-base font-medium text-blue-700">Progress</span>
                                        <span class="text-sm font-medium text-blue-700">{{ $task->progress }}%</span>
                                    </div>
                                    <div class="w-full bg-gray-200 rounded-full h-2.5">
                                        <div class="bg-blue-600 h-2.5 rounded-full" style="width: {{ $task->progress }}%"></div>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <p class="text-gray-500 text-center py-8">Anda tidak memiliki tugas harian yang aktif.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>