<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start gap-4">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    <a href="{{ route('projects.show', $project) }}" class="text-gray-400 hover:text-gray-600 transition-colors duration-200">Kegiatan: {{ $project->name }}</a> /
                    <span class="font-bold">{{ __('Dashboard Tim') }}</span>
                </h2>
                <p class="text-sm text-gray-500 mt-1">Ringkasan kinerja tim dalam kegiatan.</p>
            </div>
        </div>
    </x-slot>

    <div class="py-12 bg-gray-50"> {{-- Latar belakang konsisten --}}
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8"> {{-- Max width dan spasi konsisten --}}

            {{-- Ringkasan per Anggota --}}
            <div class="space-y-6">
                @forelse ($teamSummary as $summary)
                <div class="bg-white p-6 rounded-xl shadow-xl hover:shadow-2xl transform hover:-translate-y-1 transition-all duration-300 ease-in-out"> {{-- Kartu anggota modern --}}
                    <div class="flex items-center mb-4">
                        <div class="w-12 h-12 bg-indigo-100 text-indigo-700 rounded-full flex items-center justify-center text-xl font-bold mr-4 shadow-inner">
                            {{ strtoupper(substr($summary['member_name'], 0, 1)) }}
                        </div>
                        <h3 class="text-2xl font-bold text-gray-800">{{ $summary['member_name'] }}</h3>
                    </div>

                    {{-- Progress Bar Rata-rata --}}
                    <div class="mt-2 mb-6"> {{-- Margin lebih besar --}}
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Progress Rata-rata: <span class="text-indigo-600 font-bold">{{ $summary['average_progress'] }}%</span></label>
                        <div class="w-full bg-gray-200 rounded-full h-4 mt-1 shadow-inner"> {{-- Tinggi progress bar lebih besar, shadow-inner --}}
                            <div class="bg-gradient-to-r from-blue-500 to-indigo-600 h-4 rounded-full shadow-md" style="width: {{ $summary['average_progress'] }}%"></div> {{-- Gradien warna, shadow-md --}}
                        </div>
                    </div>

                    {{-- Statistik Detail --}}
                    <div class="grid grid-cols-2 md:grid-cols-5 gap-4 text-center"> {{-- Gap konsisten --}}
                        <div class="bg-blue-50 p-4 rounded-lg shadow-sm flex flex-col items-center justify-center transform hover:scale-105 transition-transform duration-200"> {{-- Kartu statistik mini --}}
                            <i class="fas fa-list-check fa-2x text-blue-600 mb-2"></i>
                            <p class="text-3xl font-extrabold text-blue-700">{{ $summary['total_tasks'] }}</p>
                            <p class="text-sm text-gray-600 mt-1">Total Tugas</p>
                        </div>
                        <div class="bg-yellow-50 p-4 rounded-lg shadow-sm flex flex-col items-center justify-center transform hover:scale-105 transition-transform duration-200">
                            <i class="fas fa-hourglass-start fa-2x text-yellow-600 mb-2"></i>
                            <p class="text-3xl font-extrabold text-yellow-700">{{ $summary['pending_tasks'] }}</p>
                            <p class="text-sm text-gray-600 mt-1">Menunggu</p>
                        </div>
                        <div class="bg-orange-50 p-4 rounded-lg shadow-sm flex flex-col items-center justify-center transform hover:scale-105 transition-transform duration-200">
                            <i class="fas fa-person-digging fa-2x text-orange-600 mb-2"></i>
                            <p class="text-3xl font-extrabold text-orange-700">{{ $summary['inprogress_tasks'] }}</p>
                            <p class="text-sm text-gray-600 mt-1">Dikerjakan</p>
                        </div>
                        <div class="bg-green-50 p-4 rounded-lg shadow-sm flex flex-col items-center justify-center transform hover:scale-105 transition-transform duration-200">
                            <i class="fas fa-check-double fa-2x text-green-600 mb-2"></i>
                            <p class="text-3xl font-extrabold text-green-700">{{ $summary['completed_tasks'] }}</p>
                            <p class="text-sm text-gray-600 mt-1">Selesai</p>
                        </div>
                        <div class="bg-red-50 p-4 rounded-lg shadow-sm flex flex-col items-center justify-center transform hover:scale-105 transition-transform duration-200">
                            <i class="fas fa-triangle-exclamation fa-2x text-red-600 mb-2"></i>
                            <p class="text-3xl font-extrabold text-red-700">{{ $summary['overdue_tasks'] }}</p>
                            <p class="text-sm text-red-600 mt-1">Overdue</p>
                        </div>
                    </div>
                </div>
                @empty
                <div class="bg-white p-8 rounded-xl shadow-xl text-center"> {{-- Kartu jika kosong --}}
                    <p class="text-gray-500 text-lg">Tidak ada anggota tim dalam proyek ini.</p>
                    <p class="text-gray-400 text-sm mt-2">Pastikan anggota tim telah ditambahkan pada detail proyek.</p>
                </div>
                @endforelse
            </div>
        </div>
    </div>
</x-app-layout>