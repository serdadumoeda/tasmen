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
        {{-- Menampilkan pesan sukses dari session --}}
        @if (session('success'))
            <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                <span class="block sm:inline">{{ session('success') }}</span>
            </div>
        @endif
        
        <h2 class="text-3xl font-extrabold mb-8">Dashboard</h2>

        <!-- Search and Filter Form -->
        <div class="mb-6 p-4 bg-white rounded-xl shadow">
            <form action="{{ route('projects.index') }}" method="GET" class="flex flex-col sm:flex-row items-center gap-4">
                <div class="flex-grow w-full sm:w-auto">
                    <label for="search" class="sr-only">Cari Kegiatan</label>
                    <input type="text" name="search" id="search" placeholder="Cari berdasarkan nama atau deskripsi..." value="{{ request('search') }}" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 transition duration-150">
                </div>
                <button type="submit" class="w-full sm:w-auto inline-flex items-center justify-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700">Cari</button>
            </form>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-6 mb-10">
            <x-card class="text-center transition duration-300 hover:shadow-lg">
                <i class="bi bi-file-earmark-text text-4xl text-teal-600 mb-3"></i>
                {{-- Note: $projects is a paginator instance, so count() gives items on the current page. Use total() for all items. --}}
                <h3 class="text-3xl font-bold">{{ $projects->total() }}</h3>
                <p class="text-gray-500">Kegiatan</p>
            </x-card>
            <x-card class="text-center transition duration-300 hover:shadow-lg">
                <i class="bi bi-person text-4xl text-teal-600 mb-3"></i>
                <h3 class="text-3xl font-bold">{{ $stats['users'] }}</h3>
                <p class="text-gray-500">Pengguna</p>
            </x-card>
            <x-card class="text-center transition duration-300 hover:shadow-lg">
                <i class="bi bi-journal-text text-4xl text-teal-600 mb-3"></i>
                <h3 class="text-3xl font-bold">{{ $stats['tasks'] }}</h3>
                <p class="text-gray-500">Tugas</p>
            </x-card>
            <x-card class="text-center transition duration-300 hover:shadow-lg">
                <i class="bi bi-check2-square text-4xl text-teal-600 mb-3"></i>
                <h3 class="text-3xl font-bold">{{ $stats['tasks_completed'] }}</h3>
                <p class="text-gray-500">Tugas Selesai</p>
            </x-card>
        </div>


        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2 space-y-6">
                {{-- The controller now passes a single, paginated $projects variable. --}}
                @forelse ($projects as $project)
                    {{-- The redundant PHP block is removed. We now use the model's accessors directly. --}}
                    {{-- The N+1 issue is solved because the controller eager-loads `tasks`. --}}
                    <a href="{{ route('projects.show', $project) }}" class="block bg-white p-6 rounded-xl shadow hover:shadow-lg transition">
                        <div class="flex justify-between mb-1">
                            <h4 class="font-semibold text-lg">{{ $project->name }}</h4>
                            {{-- Use the new, consistent status badge component --}}
                            <x-status-badge :status="$project->status" />
                        </div>
                        <p class="text-sm text-gray-600">Ketua: {{ $project->leader->name }}</p>
                        {{-- The budget sum is now eager-loaded via withSum for efficiency. --}}
                        <p class="text-sm text-gray-500">Anggaran: Rp. {{ number_format($project->budget_items_sum_total_cost ?? 0, 0, ',', '.') }}</p>
                        <div class="w-full bg-gray-200 h-2 mt-3 rounded-full">
                            {{-- Use the `progress` accessor from the Project model. --}}
                            <div class="bg-cyan-600 h-2 rounded-full" style="width: {{ $project->progress }}%"></div>
                        </div>
                        {{-- Use the eager-loaded tasks relationship for counts. --}}
                        <p class="text-sm text-right text-gray-500 mt-1">{{ $project->tasks->where('status', 'completed')->count() }} / {{ $project->tasks->count() }} Tugas</p>
                    </a>
                @empty
                    <x-card class="text-center">
                        <p class="text-gray-500">Anda belum memiliki kegiatan. Silakan buat yang baru!</p>
                    </x-card>
                @endforelse

                {{-- Add pagination links. This fixes the broken pagination issue. --}}
                <div class="mt-6">
                    {{ $projects->links() }}
                </div>
            </div>

            <x-card>
                <h4 class="text-lg font-bold mb-4">Aktivitas Terbaru</h4>
                <ul class="divide-y divide-gray-200 text-sm">
                    @forelse ($activities as $activity)
                        <li class="py-3">
                            <div>
                                <span class="font-semibold">{{ $activity->user->name ?? 'Pengguna' }}</span>
                                {{ $activity->description }}
                                @if ($activity->subject)
                                    <span class="text-gray-600">"{{ \Illuminate\Support\Str::limit($activity->subject->name ?? $activity->subject->title, 25) }}"</span>
                                @endif
                            </div>
                            <div class="text-xs text-gray-500 mt-1" title="{{ $activity->created_at->format('d M Y H:i') }}">
                                {{ $activity->created_at->diffForHumans() }}
                            </div>
                        </li>
                    @empty
                        <li class="py-3 text-center text-gray-500">
                            Belum ada aktivitas terbaru.
                        </li>
                    @endforelse
                </ul>
            </x-card>
        </div>
    </main>
</x-app-layout>
