<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Tim: {{ $project->name }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="container mx-auto p-4 md:p-8">

        {{-- Header --}}
        <div class="mb-6">
            <a href="{{ route('projects.show', $project) }}" class="text-blue-600 hover:text-blue-800">&larr; Kembali ke Proyek</a>
            <h1 class="text-4xl font-bold text-gray-800 mt-2">Dashboard Tim</h1>
            <p class="text-xl text-gray-600">Proyek: {{ $project->name }}</p>
        </div>

        {{-- Ringkasan per Anggota --}}
        <div class="space-y-6">
            @forelse ($teamSummary as $summary)
            <div class="bg-white p-6 rounded-lg shadow-md">
                <h3 class="text-2xl font-semibold text-gray-800">{{ $summary['member_name'] }}</h3>

                {{-- Progress Bar Rata-rata --}}
                <div class="mt-2 mb-4">
                    <label class="text-sm font-medium text-gray-600">Progress Rata-rata: {{ $summary['average_progress'] }}%</label>
                    <div class="w-full bg-gray-200 rounded-full h-4 mt-1">
                        <div class="bg-gradient-to-r from-blue-400 to-blue-600 h-4 rounded-full" style="width: {{ $summary['average_progress'] }}%"></div>
                    </div>
                </div>

                {{-- Statistik Detail --}}
                <div class="grid grid-cols-2 md:grid-cols-5 gap-4 text-center">
                    <div class="bg-gray-50 p-3 rounded">
                        <p class="text-2xl font-bold text-blue-600">{{ $summary['total_tasks'] }}</p>
                        <p class="text-sm text-gray-500">Total Tugas</p>
                    </div>
                     <div class="bg-gray-50 p-3 rounded">
                        <p class="text-2xl font-bold text-yellow-500">{{ $summary['pending_tasks'] }}</p>
                        <p class="text-sm text-gray-500">Pending</p>
                    </div>
                     <div class="bg-gray-50 p-3 rounded">
                        <p class="text-2xl font-bold text-orange-500">{{ $summary['inprogress_tasks'] }}</p>
                        <p class="text-sm text-gray-500">Dikerjakan</p>
                    </div>
                     <div class="bg-gray-50 p-3 rounded">
                        <p class="text-2xl font-bold text-green-500">{{ $summary['completed_tasks'] }}</p>
                        <p class="text-sm text-gray-500">Selesai</p>
                    </div>
                    <div class="bg-red-50 p-3 rounded">
                        <p class="text-2xl font-bold text-red-600">{{ $summary['overdue_tasks'] }}</p>
                        <p class="text-sm text-red-500">Overdue</p>
                    </div>
                </div>
            </div>
            @empty
            <div class="bg-white p-6 rounded-lg shadow-md text-center">
                <p class="text-gray-500">Tidak ada anggota tim dalam proyek ini.</p>
            </div>
            @endforelse
        </div>
    </div>
</body>
</html>