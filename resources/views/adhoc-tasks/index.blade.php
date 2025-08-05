<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Tugas Harian / Non-Proyek') }}
            </h2>
            <a href="{{ route('adhoc-tasks.create') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150 shadow-md hover:shadow-lg transform hover:scale-105"> {{-- Menyesuaikan tombol --}}
                Tambah Tugas
            </a>
        </div>
    </x-slot>

    <div class="py-12 bg-gray-50"> {{-- Menyesuaikan latar belakang dengan Executive Summary --}}
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="mb-6 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg relative shadow-md" role="alert"> {{-- Menambahkan rounded-lg dan shadow --}}
                    <span class="block sm:inline">{{ session('success') }}</span>
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg"> {{-- Mengubah shadow-sm menjadi shadow-xl --}}
                <div class="p-6 text-gray-900">

                    <!-- Filter and Search Form -->
                    <form action="{{ route('adhoc-tasks.index') }}" method="GET" class="mb-6">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div class="md:col-span-2">
                                <label for="search" class="sr-only">Cari</label>
                                <input type="text" name="search" id="search" placeholder="Cari berdasarkan judul atau deskripsi..." value="{{ request('search') }}" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 transition">
                            </div>
                            @if(Auth::user()->canManageUsers())
                            <div>
                                <label for="personnel_id" class="sr-only">Filter Personel</label>
                                <select name="personnel_id" id="personnel_id" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 transition">
                                    <option value="">Semua Personel</option>
                                    @foreach($subordinates as $subordinate)
                                        <option value="{{ $subordinate->id }}" @selected(request('personnel_id') == $subordinate->id)>{{ $subordinate->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            @endif
                            <div>
                                <button type="submit" class="w-full inline-flex justify-center items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:outline-none">Cari / Filter</button>
                            </div>
                        </div>
                    </form>

                    <div class="space-y-6"> {{-- Meningkatkan spasi antar kartu --}}
                        @forelse ($assignedTasks as $task)
                            <div class="block p-6 border border-gray-200 rounded-xl bg-white shadow-lg hover:shadow-xl transform hover:-translate-y-1 transition-all duration-300 ease-in-out"> {{-- Kartu tugas lebih besar, rounded-xl, shadow, dan efek hover --}}
                                <div class="flex justify-between items-start">
                                    <div>
                                        <p class="font-bold text-xl text-indigo-700 mb-2">{{ $task->title }}</p> {{-- Ukuran teks lebih besar --}}
                                        <div class="text-sm text-gray-600 space-x-3"> {{-- Warna teks lebih gelap, spasi lebih baik --}}
                                            <span class="inline-flex items-center"><i class="far fa-calendar-alt text-gray-400 mr-1"></i> Deadline: {{ \Carbon\Carbon::parse($task->deadline)->format('d M Y') }}</span>
                                            <span class="inline-flex items-center"><i class="far fa-clock text-gray-400 mr-1"></i> Estimasi: {{ $task->estimated_hours }} jam</span>
                                            <span class="inline-flex items-center"><i class="fas fa-users text-gray-400 mr-1"></i> Ditugaskan ke: 
                                                @foreach($task->assignees as $assignee)
                                                    <span class="font-medium text-gray-800">{{ $assignee->name }}{{ !$loop->last ? ',' : '' }}</span>
                                                @endforeach
                                            </span>
                                        </div>
                                    </div>
                                    <div class="flex items-center space-x-3 flex-shrink-0"> {{-- Spasi tombol --}}
                                        <a href="{{ route('adhoc-tasks.edit', $task) }}" class="inline-flex items-center px-3 py-1.5 bg-indigo-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-600 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150 shadow-sm hover:shadow-md"> {{-- Tombol Detail/Edit lebih menonjol --}}
                                            <i class="fas fa-edit mr-1"></i> Detail/Edit
                                        </a>
                                        {{-- Jika ada tombol delete atau complete, bisa ditambahkan di sini --}}
                                    </div>
                                </div>
                                <div class="mt-4"> {{-- Margin atas lebih besar --}}
                                    <div class="flex justify-between mb-2 items-center"> {{-- Menambahkan items-center --}}
                                        <span class="text-base font-semibold text-blue-700">Progress</span> {{-- Lebih tebal --}}
                                        <span class="text-lg font-bold text-blue-700">{{ $task->progress }}%</span> {{-- Ukuran dan ketebalan teks lebih besar --}}
                                    </div>
                                    <div class="w-full bg-gray-200 rounded-full h-3"> {{-- Tinggi progress bar lebih besar --}}
                                        <div class="bg-blue-600 h-3 rounded-full shadow-inner" style="width: {{ $task->progress }}%"></div> {{-- Shadow inner pada progress bar --}}
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="bg-gray-50 p-6 rounded-xl shadow-md text-center py-10"> {{-- Menyesuaikan tampilan jika tidak ada tugas --}}
                                <p class="text-gray-500 text-lg">Tidak ada tugas harian yang cocok dengan kriteria Anda.</p>
                                <a href="{{ route('adhoc-tasks.index') }}" class="mt-4 text-sm text-indigo-600 hover:underline">Hapus Filter</a>
                            </div>
                        @endforelse
                    </div>

                    <div class="mt-8">
                        {{ $assignedTasks->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>