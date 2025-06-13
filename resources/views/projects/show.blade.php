<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Proyek: {{ $project->name }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    {{-- Script untuk library Chart.js --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .progress-bar { transition: width 0.6s ease; }
    </style>
</head>
<body class="bg-gray-100">
    <div class="container mx-auto p-4 md:p-8">

        <div class="mb-6">
            <a href="{{ route('dashboard') }}" class="text-blue-600 hover:text-blue-800">&larr; Kembali ke Dashboard</a>
            <div class="flex flex-wrap items-center justify-between gap-4 mt-2">
                <h1 class="text-4xl font-bold text-gray-800">{{ $project->name }}</h1>
                {{-- Tombol ini hanya muncul untuk Ketua Tim --}}
                @can('viewTeamDashboard', $project)
                    <a href="{{ route('projects.team.dashboard', $project) }}" class="inline-block bg-blue-600 text-white font-bold text-sm px-4 py-2 rounded-lg shadow-md hover:bg-blue-700 transition">
                        Lihat Dashboard Tim
                    </a>
                @endcan
            </div>
        </div>

        @if (session('success'))
            <div class="mb-4 bg-green-100 border-l-4 border-green-500 text-green-700 p-4" role="alert">
                <p>{{ session('success') }}</p>
            </div>
        @endif

        <div class="mb-8">
            <h2 class="text-2xl font-semibold text-gray-700 mb-4">Ringkasan Proyek</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="bg-white p-4 rounded-lg shadow-md text-center">
                    <p class="text-3xl font-bold text-blue-600">{{ $stats['total'] }}</p>
                    <p class="text-gray-500">Total Tugas</p>
                </div>
                <div class="bg-white p-4 rounded-lg shadow-md text-center">
                    <p class="text-3xl font-bold text-yellow-500">{{ $stats['pending'] }}</p>
                    <p class="text-gray-500">Tugas Pending</p>
                </div>
                <div class="bg-white p-4 rounded-lg shadow-md text-center">
                    <p class="text-3xl font-bold text-orange-500">{{ $stats['in_progress'] }}</p>
                    <p class="text-gray-500">Sedang Dikerjakan</p>
                </div>
                <div class="bg-white p-4 rounded-lg shadow-md text-center">
                    <p class="text-3xl font-bold text-green-500">{{ $stats['completed'] }}</p>
                    <p class="text-gray-500">Tugas Selesai</p>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <div class="lg:col-span-2 space-y-6">

                <div class="bg-white p-6 rounded-lg shadow-md">
                    <h3 class="text-xl font-semibold mb-4 border-b pb-2">Tambah Tugas Baru</h3>
                    <form action="{{ route('tasks.store', $project) }}" method="POST">
                        @csrf
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="title" class="block text-sm font-medium text-gray-700">Judul Tugas</label>
                                <input type="text" name="title" id="title" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required>
                            </div>
                            <div>
                                <label for="deadline" class="block text-sm font-medium text-gray-700">Deadline</label>
                                <input type="date" name="deadline" id="deadline" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required>
                            </div>
                            <div class="md:col-span-2">
                                <label for="assigned_to_id" class="block text-sm font-medium text-gray-700">Tugaskan Kepada</label>
                                <select name="assigned_to_id" id="assigned_to_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required>
                                    <option value="">-- Pilih Anggota --</option>
                                    @foreach($projectMembers as $member)
                                    <option value="{{ $member->id }}">{{ $member->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <button type="submit" class="mt-4 inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700">Tambah Tugas</button>
                    </form>
                </div>

                <div class="bg-white p-6 rounded-lg shadow-md">
                    <h3 class="text-xl font-semibold mb-4 border-b pb-2">Daftar Tugas</h3>
                    <div class="space-y-4">
                        @forelse($project->tasks as $task)
                            @php
                                $isOverdue = $task->deadline < now() && $task->status != 'completed';
                            @endphp
                            <div class="border p-4 rounded-md @if($isOverdue) border-red-500 bg-red-50 @endif">
                                <div class="flex justify-between items-start">
                                    <div>
                                        <h4 class="font-bold text-lg">{{ $task->title }}</h4>
                                        <p class="text-sm text-gray-600">Untuk: <strong>{{ $task->assignedTo->name }}</strong> | Deadline: 
                                            <span class="@if($isOverdue) text-red-700 font-bold @endif">
                                                {{ \Carbon\Carbon::parse($task->deadline)->format('d M Y') }}
                                            </span>
                                        </p>
                                    </div>
                                    <div class="flex space-x-2 flex-shrink-0">
                                        <a href="{{ route('tasks.edit', $task) }}" class="text-sm text-yellow-600 hover:text-yellow-800 font-medium">Edit</a>
                                        <form action="{{ route('tasks.destroy', $task) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus tugas ini?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-sm text-red-600 hover:text-red-800 font-medium">Hapus</button>
                                        </form>
                                    </div>
                                </div>
                                <div class="mt-2">
                                    <div class="flex justify-between mb-1">
                                        <span class="text-base font-medium text-blue-700">Progress</span>
                                        <span class="text-sm font-medium text-blue-700">{{ $task->progress }}%</span>
                                    </div>
                                    <div class="w-full bg-gray-200 rounded-full h-2.5">
                                        <div class="bg-blue-600 h-2.5 rounded-full progress-bar" style="width: {{ $task->progress }}%"></div>
                                    </div>
                                </div>
                                <div class="mt-4 border-t pt-4">
                                    <h5 class="font-semibold text-sm mb-2">Lampiran</h5>
                                    <ul class="list-disc list-inside space-y-1 mb-3">
                                        @forelse($task->attachments as $attachment)
                                            <li class="text-sm flex justify-between items-center">
                                                <a href="{{ asset('storage/' . $attachment->path) }}" target="_blank" class="text-blue-600 hover:underline">{{ $attachment->filename }}</a>
                                                <form action="{{ route('attachments.destroy', $attachment) }}" method="POST">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-xs text-red-500 hover:text-red-700">&times;</button>
                                                </form>
                                            </li>
                                        @empty
                                            <li class="text-sm text-gray-500 list-none">Belum ada lampiran.</li>
                                        @endforelse
                                    </ul>
                                    <form action="{{ route('tasks.attachments.store', $task) }}" method="POST" enctype="multipart/form-data">
                                        @csrf
                                        <div class="flex items-center space-x-2">
                                            <input type="file" name="file" class="text-sm" required>
                                            <button type="submit" class="inline-flex items-center px-3 py-1 bg-gray-700 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-600">Unggah</button>
                                        </div>
                                    </form>
                                </div>
                                <div class="mt-4 border-t pt-4">
                                    <h5 class="font-semibold text-sm mb-2">Diskusi</h5>
                                    <div class="space-y-3 mb-4">
                                        @forelse($task->comments as $comment)
                                        <div class="flex items-start space-x-2 text-sm">
                                            <span class="font-bold text-gray-800">{{ $comment->user->name }}:</span>
                                            <p class="text-gray-700">{{ $comment->body }}</p>
                                        </div>
                                        @empty
                                        <p class="text-sm text-gray-500">Belum ada komentar.</p>
                                        @endforelse
                                    </div>
                                    <form action="{{ route('tasks.comments.store', $task) }}" method="POST">
                                        @csrf
                                        <div class="flex space-x-2">
                                            <input type="text" name="body" class="flex-grow block w-full rounded-md border-gray-300 shadow-sm text-sm" placeholder="Tulis komentar..." required>
                                            <button type="submit" class="inline-flex items-center px-3 py-1 bg-gray-700 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-600">Kirim</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        @empty
                            <p class="text-gray-500">Belum ada tugas di proyek ini.</p>
                        @endforelse
                    </div>
                </div>
            </div>

            <div class="lg:col-span-1 space-y-6">

                <div class="bg-white p-6 rounded-lg shadow-md">
                    <h3 class="text-xl font-semibold mb-2">Distribusi Status Tugas</h3>
                    <canvas id="taskStatusChart"></canvas>
                </div>

                <div class="bg-white p-6 rounded-lg shadow-md">
                    <h3 class="text-xl font-semibold mb-2">Detail Proyek</h3>
                    <p class="text-gray-700">{{ $project->description }}</p>
                </div>
                
                <div class="bg-white p-6 rounded-lg shadow-md">
                    <h3 class="text-xl font-semibold mb-2">Tim Proyek</h3>
                    <ul>
                        <li class="flex items-center space-x-2">
                            <span class="font-bold">Ketua Tim:</span>
                            <span>{{ $project->leader->name }}</span>
                        </li>
                    </ul>
                    <h4 class="font-semibold mt-4">Anggota:</h4>
                    <ul class="list-disc list-inside mt-2">
                        @foreach($project->members as $member)
                            <li>{{ $member->name }}</li>
                        @endforeach
                    </ul>
                </div>
                
                <div class="bg-white p-6 rounded-lg shadow-md">
                    <h3 class="text-xl font-semibold mb-2">Beban Tugas Tim</h3>
                    <ul class="space-y-2">
                        @foreach($project->members as $member)
                            @php
                                $taskCount = $tasksByUser->get($member->id, collect())->count();
                            @endphp
                            <li class="flex justify-between items-center text-sm">
                                <span class="text-gray-700">{{ $member->name }}</span>
                                <span class="font-bold px-2 py-1 text-xs rounded {{ $taskCount > 0 ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800' }}">
                                    {{ $taskCount }} Tugas
                                </span>
                            </li>
                        @endforeach
                    </ul>
                </div>

                <div class="bg-white p-6 rounded-lg shadow-md">
                    <h3 class="text-xl font-semibold mb-2">Aktivitas Terbaru</h3>
                    <ul class="space-y-2">
                        @foreach($project->activities->take(10) as $activity)
                            <li class="text-sm text-gray-600">
                                <span class="font-semibold text-gray-800">{{ $activity->user->name }}</span>
                                @switch($activity->description)
                                    @case('created_project')
                                        membuat proyek ini
                                        @break
                                    @case('updated_project')
                                        memperbarui proyek ini
                                        @break
                                    @case('created_task')
                                        membuat tugas "{{ $activity->subject->title ?? 'Tugas yang telah dihapus' }}"
                                        @break
                                    @case('updated_task')
                                        memperbarui tugas "{{ $activity->subject->title ?? 'Tugas yang telah dihapus' }}"
                                        @break
                                    @case('deleted_task')
                                        menghapus sebuah tugas
                                        @break
                                    @default
                                        melakukan sebuah aktivitas
                                @endswitch
                                <span class="block text-xs text-gray-400">{{ $activity->created_at->diffForHumans() }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>

            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const ctx = document.getElementById('taskStatusChart');
            const stats = @json($stats);

            new Chart(ctx, {
                type: 'pie',
                data: {
                    labels: ['Pending', 'Dikerjakan', 'Selesai'],
                    datasets: [{
                        label: 'Jumlah Tugas',
                        data: [stats.pending, stats.in_progress, stats.completed],
                        backgroundColor: [
                            'rgb(253, 224, 71)', // yellow-400
                            'rgb(249, 115, 22)', // orange-500
                            'rgb(34, 197, 94)'  // green-500
                        ],
                        hoverOffset: 4
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'top',
                        },
                        title: {
                            display: false,
                            text: 'Distribusi Status Tugas'
                        }
                    }
                }
            });
        });
    </script>
</body>
</html>