<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Global Dashboard Pengawasan') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            <div class="mb-8">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    <div class="bg-white p-4 rounded-lg shadow text-center">
                        <p class="text-3xl font-bold text-blue-600">{{ $stats['total_projects'] }}</p>
                        <p class="text-gray-500">Total Proyek</p>
                    </div>
                    <div class="bg-white p-4 rounded-lg shadow text-center">
                        <p class="text-3xl font-bold text-gray-600">{{ $stats['total_users'] }}</p>
                        <p class="text-gray-500">Total Pengguna</p>
                    </div>
                    <div class="bg-white p-4 rounded-lg shadow text-center">
                        <p class="text-3xl font-bold text-orange-500">{{ $stats['total_tasks'] }}</p>
                        <p class="text-gray-500">Total Tugas</p>
                    </div>
                    <div class="bg-white p-4 rounded-lg shadow text-center">
                        <p class="text-3xl font-bold text-green-500">{{ $stats['completed_tasks'] }}</p>
                        <p class="text-gray-500">Tugas Selesai</p>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <div class="lg:col-span-2 bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-medium mb-4 text-gray-900">Semua Proyek</h3>
                    <div class="space-y-4">
                        @foreach ($allProjects as $project)
                            <a href="{{ route('projects.show', $project) }}" class="block p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition">
                                <div class="flex justify-between">
                                    <div>
                                        <p class="font-bold text-blue-600">{{ $project->name }}</p>
                                        <p class="text-sm text-gray-600">Ketua: {{ $project->leader->name }}</p>
                                    </div>
                                    <div class="text-right">
                                        <p class="font-semibold text-gray-700">{{ $project->tasks->where('status', 'completed')->count() }} / {{ $project->tasks->count() }} Tugas</p>
                                        <p class="text-sm text-gray-500">Selesai</p>
                                    </div>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                     <h3 class="text-lg font-medium mb-4 text-gray-900">Aktivitas Terbaru Sistem</h3>
                     <ul class="space-y-3">
                        @foreach($recentActivities as $activity)
                            <li class="text-sm text-gray-600 border-b border-gray-200 pb-2">
                                <span class="font-semibold text-gray-800">{{ $activity->user->name }}</span>
                                @switch($activity->description)
                                    @case('created_project') membuat proyek "{{ $activity->subject->name ?? '' }}" @break
                                    @case('created_task') membuat tugas "{{ $activity->subject->title ?? '...' }}" @break
                                    @case('updated_task') memperbarui tugas "{{ $activity->subject->title ?? '...' }}" @break
                                    @case('deleted_task') menghapus sebuah tugas @break
                                    @default melakukan sebuah aktivitas @break
                                @endswitch
                                <span class="block text-xs text-gray-400">{{ $activity->created_at->diffForHumans() }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
