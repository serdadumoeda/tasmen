<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            <i class="fas fa-tachometer-alt mr-2"></i> {{ __('Pusat Kontrol Sistem') }}
        </h2>
    </x-slot>

    <div class="py-12 bg-gray-100">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            <!-- Grid Metrik Utama -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <!-- Total Proyek -->
                <div class="bg-white p-6 rounded-xl shadow-xl flex items-center space-x-4">
                    <div class="bg-blue-500 p-4 rounded-full">
                        <i class="fas fa-folder-open fa-2x text-white"></i>
                    </div>
                    <div>
                        <p class="text-3xl font-bold text-gray-800">{{ $stats['total_projects'] }}</p>
                        <p class="text-gray-500 font-medium">Total Proyek</p>
                    </div>
                </div>
                <!-- Pengguna Aktif -->
                <div class="bg-white p-6 rounded-xl shadow-xl flex items-center space-x-4">
                    <div class="bg-green-500 p-4 rounded-full">
                        <i class="fas fa-users fa-2x text-white"></i>
                    </div>
                    <div>
                        <p class="text-3xl font-bold text-gray-800">{{ $stats['active_users'] }}/<span class="text-2xl text-gray-600">{{ $stats['total_users'] }}</span></p>
                        <p class="text-gray-500 font-medium">Pengguna Aktif</p>
                    </div>
                </div>
                <!-- Total Tugas -->
                <div class="bg-white p-6 rounded-xl shadow-xl flex items-center space-x-4">
                    <div class="bg-orange-500 p-4 rounded-full">
                        <i class="fas fa-tasks fa-2x text-white"></i>
                    </div>
                    <div>
                        <p class="text-3xl font-bold text-gray-800">{{ $stats['completed_tasks'] }}/<span class="text-2xl text-gray-600">{{ $stats['total_tasks'] }}</span></p>
                        <p class="text-gray-500 font-medium">Tugas Selesai</p>
                    </div>
                </div>
                <!-- Permintaan Tertunda -->
                <div class="bg-white p-6 rounded-xl shadow-xl flex items-center space-x-4">
                    <div class="bg-yellow-500 p-4 rounded-full">
                        <i class="fas fa-inbox fa-2x text-white"></i>
                    </div>
                    <div>
                        <p class="text-3xl font-bold text-gray-800">{{ $stats['pending_requests'] }}</p>
                        <p class="text-gray-500 font-medium">Permintaan Tertunda</p>
                    </div>
                </div>
            </div>

            <!-- Grid Konten Utama (Chart dan Aktivitas) -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

                <!-- Kolom Kiri: Chart Status Proyek -->
                <div class="lg:col-span-2 bg-white overflow-hidden shadow-xl rounded-xl p-6">
                    <h3 class="text-lg font-semibold mb-4 text-gray-900 flex items-center">
                        <i class="fas fa-chart-pie mr-3 text-indigo-500"></i>
                        Distribusi Status Proyek
                    </h3>
                    <div class="h-80">
                        <canvas id="projectStatusChart"></canvas>
                    </div>
                </div>
                <!-- Kolom Kanan: Aktivitas Terbaru -->
                <div class="bg-white overflow-hidden shadow-xl rounded-xl p-6">
                     <h3 class="text-lg font-semibold mb-4 text-gray-900 flex items-center">
                        <i class="fas fa-history mr-3 text-indigo-500"></i>
                        Aktivitas Terbaru Sistem
                    </h3>
                     <ul class="space-y-4">
                        @forelse($recentActivities as $activity)
                            <li class="flex items-start space-x-3">
                                <div class="flex-shrink-0 pt-1">
                                    @switch($activity->description)
                                        @case('created_project') <i class="fas fa-folder-plus text-blue-500"></i> @break
                                        @case('created_task') <i class="fas fa-check-circle text-green-500"></i> @break
                                        @case('updated_task') <i class="fas fa-edit text-yellow-500"></i> @break
                                        @case('created_user') <i class="fas fa-user-plus text-purple-500"></i> @break
                                        @default <i class="fas fa-dot-circle text-gray-400"></i> @break
                                    @endswitch
                                </div>
                                <div class="flex-1">
                                    <p class="text-sm text-gray-700">
                                        <span class="font-bold text-gray-800">{{ optional($activity->user)->name ?? 'Sistem' }}</span>
                                        @switch($activity->description)
                                            @case('created_project') membuat proyek baru @break
                                            @case('created_task') membuat tugas baru @break
                                            @case('updated_task') memperbarui sebuah tugas @break
                                            @case('created_user') mendaftarkan pengguna baru @break
                                            @default melakukan sebuah aktivitas @break
                                        @endswitch
                                        <span class="font-semibold text-indigo-600">{{ optional($activity->subject)->name ?? optional($activity->subject)->title ?? '' }}</span>
                                    </p>
                                    <p class="text-xs text-gray-400 mt-0.5">{{ $activity->created_at->diffForHumans() }}</p>
                                </div>
                            </li>
                        @empty
                            <li class="text-center text-gray-500 py-8">
                                <i class="fas fa-box-open fa-2x mb-2"></i>
                                <p>Belum ada aktivitas tercatat.</p>
                            </li>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const ctx = document.getElementById('projectStatusChart').getContext('2d');
            new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: @json($chartData['labels']),
                    datasets: [{
                        label: 'Status Proyek',
                        data: @json($chartData['data']),
                        backgroundColor: [
                            'rgba(34, 197, 94, 0.7)',  // green-500
                            'rgba(239, 68, 68, 0.7)',  // red-500
                            'rgba(59, 130, 246, 0.7)', // blue-500
                            'rgba(168, 85, 247, 0.7)'  // purple-500
                        ],
                        borderColor: [
                            'rgba(34, 197, 94, 1)',
                            'rgba(239, 68, 68, 1)',
                            'rgba(59, 130, 246, 1)',
                            'rgba(168, 85, 247, 1)'
                        ],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    let label = context.label || '';
                                    if (label) {
                                        label += ': ';
                                    }
                                    if (context.parsed !== null) {
                                        label += context.parsed;
                                    }
                                    return label + ' Proyek';
                                }
                            }
                        }
                    }
                }
            });
        });
    </script>
    @endpush
</x-app-layout>