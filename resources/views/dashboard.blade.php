<x-app-layout>
    {{-- Menggunakan header dinamis dari layout asli --}}
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-2xl font-bold leading-tight text-gray-800">
                    Daftar Kegiatan
                </h2>
                <p class="mt-1 text-sm text-gray-500">
                    Kelola semua kegiatan dan proyek yang sedang berjalan di sini.
                </p>
            </div>
        <div class="mt-4 sm:mt-0 sm:ml-4 flex items-center space-x-2">
            <x-secondary-button :href="route('projects.workflow')">
                <i class="fas fa-sitemap mr-2"></i>
                Lihat Alur Kerja
            </x-secondary-button>
            @can('create', App\Models\Project::class)
                    <x-primary-button :href="route('projects.create.step1')">
                        <i class="fas fa-plus mr-2"></i>
                        Inisiasi Kegiatan Baru
                    </x-primary-button>
                @endcan
            </div>
        </div>
    </x-slot>

    <main class="max-w-screen-2xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
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
                <x-primary-button type="submit" class="w-full sm:w-auto justify-center">
                    Cari
                </x-primary-button>
            </form>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-6 mb-10">
            <x-card class="text-center">
                <i class="fas fa-file-alt text-4xl text-teal-600 mb-3"></i>
                {{-- Note: $projects is a paginator instance, so count() gives items on the current page. Use total() for all items. --}}
                <h3 class="text-3xl font-bold">{{ $projects->total() }}</h3>
                <p class="text-gray-500">Kegiatan</p>
            </x-card>
            <x-card class="text-center">
                <i class="fas fa-users text-4xl text-teal-600 mb-3"></i>
                <h3 class="text-3xl font-bold">{{ $stats['users'] }}</h3>
                <p class="text-gray-500">Pengguna</p>
            </x-card>
            <x-card class="text-center">
                <i class="fas fa-tasks text-4xl text-teal-600 mb-3"></i>
                <h3 class="text-3xl font-bold">{{ $stats['tasks'] }}</h3>
                <p class="text-gray-500">Tugas</p>
            </x-card>
            <x-card class="text-center">
                <i class="fas fa-check-double text-4xl text-teal-600 mb-3"></i>
                <h3 class="text-3xl font-bold">{{ $stats['tasks_completed'] }}</h3>
                <p class="text-gray-500">Tugas Selesai</p>
            </x-card>
        </div>


        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2 space-y-6">
                {{-- The controller now passes a single, paginated $projects variable. --}}
                @if ($projects->isNotEmpty())
                    @foreach ($projects as $project)
                        {{-- The redundant PHP block is removed. We now use the model's accessors directly. --}}
                        {{-- The N+1 issue is solved because the controller eager-loads `tasks`. --}}
                        <x-card as="a" href="{{ route('projects.show', $project) }}" class="block">
                            <div class="flex justify-between mb-1">
                                <h4 class="font-semibold text-lg">{{ $project->name }}</h4>
                                {{-- Use the new, consistent status badge component --}}
                                <x-status-badge :status="$project->status" />
                            </div>
                            <p class="text-sm text-gray-600">Ketua: {{ $project->leader->name }}</p>
                            <p class="text-sm text-gray-500">Anggaran: Rp. {{ number_format($project->budget_items_sum_total_cost ?? 0, 0, ',', '.') }}</p>
                            @if($project->description)
                                <p class="text-sm text-gray-500 mt-2 mb-3 border-l-4 border-gray-200 pl-3">
                                    {{ Str::limit($project->description, 100) }}
                                </p>
                            @endif
                            @php
                                $progress = ($project->tasks_count > 0) ? round(($project->completed_tasks_count / $project->tasks_count) * 100) : 0;
                            @endphp
                            <div class="w-full bg-gray-200 h-2 mt-3 rounded-full">
                                <div class="bg-cyan-600 h-2 rounded-full" style="width: {{ $progress }}%"></div>
                            </div>
                            <p class="text-sm text-right text-gray-500 mt-1">{{ $project->completed_tasks_count }} / {{ $project->tasks_count }} Tugas</p>
                        </a>
                    @endforeach
                @else
                    <div class="text-center bg-white p-12 rounded-xl border border-gray-200 shadow-sm">
                        {{-- 1. Visual --}}
                        <svg class="mx-auto h-12 w-12 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                          <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 9.776c.112-.017.227-.026.344-.026h15.812c.117 0 .232.009.344.026m-16.5 0a2.25 2.25 0 00-1.883 2.542l.857 6a2.25 2.25 0 002.227 1.932H19.05a2.25 2.25 0 002.227-1.932l.857-6a2.25 2.25 0 00-1.883-2.542m-16.5 0A2.25 2.25 0 015.625 7.5h12.75c1.135 0 2.097.79 2.234 1.883m-16.5 0a2.25 2.25 0 00-1.883-2.542l.857-6a2.25 2.25 0 002.227-1.932H19.05a2.25 2.25 0 002.227-1.932l.857 6a2.25 2.25 0 00-1.883 2.542z" />
                        </svg>

                        {{-- 2. Pesan --}}
                        <h3 class="mt-2 text-lg font-semibold text-gray-900">Belum Ada Kegiatan</h3>
                        <p class="mt-1 text-sm text-gray-500">Mulai bekerja dengan menginisiasi kegiatan atau proyek baru.</p>

                        {{-- 3. Call to Action (CTA) --}}
                        <div class="mt-6">
                            <x-primary-button :href="route('projects.create.step1')">
                                <i class="fas fa-plus mr-2"></i>
                                Inisiasi Kegiatan Baru
                            </x-primary-button>
                        </div>
                    </div>
                @endif

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
