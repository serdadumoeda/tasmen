<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Proyek: {{ $project->name }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .progress-bar { transition: width 0.6s ease; }
    </style>
</head>
<body class="bg-gray-100">
    <div class="container mx-auto p-4 md:p-8">
        
        {{-- Tombol Kembali & Header --}}
        <div class="mb-6">
            <a href="{{ route('dashboard') }}" class="text-blue-600 hover:text-blue-800">&larr; Kembali ke Dashboard</a>
            <h1 class="text-4xl font-bold text-gray-800 mt-2">{{ $project->name }}</h1>
        </div>
        
        {{-- Notifikasi Sukses --}}
        @if (session('success'))
            <div class="mb-4 bg-green-100 border-l-4 border-green-500 text-green-700 p-4" role="alert">
                <p>{{ session('success') }}</p>
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            {{-- Kolom Kiri: Tugas & Form Tambah Tugas --}}
            <div class="lg:col-span-2 space-y-6">
                
                {{-- Form Tambah Tugas Baru --}}
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

                {{-- Daftar Tugas --}}
                <div class="bg-white p-6 rounded-lg shadow-md">
                    <h3 class="text-xl font-semibold mb-4 border-b pb-2">Daftar Tugas</h3>
                    <div class="space-y-4">
                        @forelse($project->tasks as $task)
                            <div class="border p-4 rounded-md">
                                <div class="flex justify-between items-start">
                                    <div>
                                        <h4 class="font-bold text-lg">{{ $task->title }}</h4>
                                        <p class="text-sm text-gray-600">Untuk: <strong>{{ $task->assignedTo->name }}</strong> | Deadline: {{ \Carbon\Carbon::parse($task->deadline)->format('d M Y') }}</p>
                                    </div>
                                    <div class="flex space-x-2">
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
                            </div>
                        @empty
                            <p class="text-gray-500">Belum ada tugas di proyek ini.</p>
                        @endforelse
                    </div>
                </div>
            </div>

            {{-- Kolom Kanan: Detail & Anggota Tim --}}
            <div class="space-y-6">
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
            </div>
        </div>
    </div>
</body>
</html>