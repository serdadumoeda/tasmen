<x-app-layout>
    {{-- Menggunakan header dinamis dari layout asli --}}
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Daftar Kegiatan') }}
            </h2>
             @can('create', App\Models\Project::class)
                <a href="{{ route('projects.create.step1') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150 shadow-md hover:shadow-lg">
                    <i class="fas fa-plus-circle mr-2"></i> Buat Kegiatan Baru
                </a>
            @endcan
        </div>
    </x-slot>

    <main class="max-w-7xl mx-auto px-4 py-10">
        @php
            // Gabungkan proyek yang dimiliki dan diikuti, lalu pastikan tidak ada duplikat
            $projects = $ownedProjects->merge($memberProjects)->unique('id');
        @endphp

        {{-- Menampilkan pesan sukses dari session --}}
        @if (session('success'))
            <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                <span class="block sm:inline">{{ session('success') }}</span>
            </div>
        @endif
        
        <h2 class="text-3xl font-extrabold mb-8">Dashboard</h2>

        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-6 mb-10">
            <div class="bg-white p-6 rounded-xl shadow hover:shadow-lg transition duration-300 text-center">
                <i class="bi bi-file-earmark-text text-4xl text-teal-600 mb-3"></i>
                <h3 class="text-3xl font-bold">{{ $projects->count() }}</h3>
                <p class="text-gray-500">Proyek</p>
            </div>
            <div class="bg-white p-6 rounded-xl shadow hover:shadow-lg transition duration-300 text-center">
                <i class="bi bi-person text-4xl text-teal-600 mb-3"></i>
                <h3 class="text-3xl font-bold">18</h3>
                <p class="text-gray-500">Pengguna</p>
            </div>
            <div class="bg-white p-6 rounded-xl shadow hover:shadow-lg transition duration-300 text-center">
                <i class="bi bi-journal-text text-4xl text-teal-600 mb-3"></i>
                <h3 class="text-3xl font-bold">2</h3>
                <p class="text-gray-500">Tugas</p>
            </div>
            <div class="bg-white p-6 rounded-xl shadow hover:shadow-lg transition duration-300 text-center">
                <i class="bi bi-check2-square text-4xl text-teal-600 mb-3"></i>
                <h3 class="text-3xl font-bold">0</h3>
                <p class="text-gray-500">Tugas Selesai</p>
            </div>
        </div>


        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2 space-y-6">
                @forelse ($projects as $project)
                    @php
                        $totalTasks = $project->tasks->count();
                        $completedTasks = $project->tasks->where('status', 'completed')->count();
                        $completionPercentage = ($totalTasks > 0) ? round(($completedTasks / $totalTasks) * 100) : 0;
                        $statusInfo = $project->getStatusAttribute(); // Menggunakan accessor untuk status
                        $statusClass = $project->getStatusColorClassAttribute();
                    @endphp
                    <a href="{{ route('projects.show', $project) }}" class="block bg-white p-6 rounded-xl shadow hover:shadow-lg transition">
                        <div class="flex justify-between mb-1">
                            <h4 class="font-semibold text-lg">{{ $project->name }}</h4>
                            <span class="text-sm {{ $statusClass }} px-3 py-1 rounded-full">{{ \Illuminate\Support\Str::title(str_replace('_', ' ', $statusInfo)) }}</span>
                        </div>
                        <p class="text-sm text-gray-600">Ketua: {{ $project->leader->name }}</p>
                        <p class="text-sm text-gray-500">Anggaran: Rp. {{ number_format($project->budget_items_sum_total_cost ?? 0, 0, ',', '.') }}</p>
                        <div class="w-full bg-gray-200 h-2 mt-3 rounded-full">
                            <div class="bg-cyan-600 h-2 rounded-full" style="width: {{ $completionPercentage }}%"></div>
                        </div>
                        <p class="text-sm text-right text-gray-500 mt-1">{{ $completedTasks }} / {{ $totalTasks }} Tugas</p>
                    </a>
                @empty
                    <div class="bg-white p-6 rounded-xl shadow text-center">
                        <p class="text-gray-500">Anda belum memiliki proyek. Silakan buat yang baru!</p>
                    </div>
                @endforelse
            </div>

            <div class="bg-white p-6 rounded-xl shadow">
                <h4 class="text-lg font-bold mb-4">Aktifitas Terbaru</h4>
                <ul class="divide-y divide-gray-200 text-sm">
                    <li class="py-3">
                        <div><strong>Super Admin</strong> memperbarui tugas "BackEnd Halaman Utama"</div>
                        <div class="text-xs text-gray-500 mt-1">1 Jam Yang Lalu</div>
                    </li>
                    <li class="py-3">
                        <div><strong>Super Admin</strong> memperbarui tugas "BackEnd Halaman Utama"</div>
                        <div class="text-xs text-gray-500 mt-1">1 Jam Yang Lalu</div>
                    </li>
                </ul>
            </div>
        </div>
    </main>
</x-app-layout>
