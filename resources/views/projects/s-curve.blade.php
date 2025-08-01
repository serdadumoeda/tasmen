<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            <a href="{{ route('projects.show', $project) }}" class="text-gray-400 hover:text-gray-600 transition-colors duration-200">Proyek: {{ $project->name }}</a> / 
            <span class="font-bold">{{ __('Kurva S') }}</span>
        </h2>
    </x-slot>

    <div class="py-12 bg-gray-50"> {{-- Latar belakang konsisten --}}
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg"> {{-- Shadow dan rounded-lg konsisten --}}
                <div class="p-6 text-gray-900">
                    <a href="{{ route('projects.show', $project) }}" class="inline-flex items-center text-indigo-600 hover:text-indigo-800 font-medium mb-4 transition-colors duration-200">
                        <i class="fas fa-arrow-left mr-2"></i> Kembali ke Detail Proyek
                    </a>
                    
                    <div class="bg-gray-50 p-5 rounded-lg border border-gray-200 shadow-sm mb-6 space-y-3">
                        <div class="flex items-center justify-between flex-wrap gap-3">
                            <p class="text-base text-gray-700 flex items-center">
                                <i class="fas fa-info-circle mr-3 text-blue-500 fa-lg"></i>
                                Grafik ini membandingkan akumulasi jam kerja yang direncanakan (biru) dengan jam kerja aktual yang tercatat (hijau).
                            </p>
                            <p class="text-lg font-bold text-gray-800 flex items-center flex-shrink-0">
                                <i class="fas fa-hourglass-half mr-2 text-indigo-600"></i> Total Jam Direncanakan: <span class="text-indigo-700 ml-2">{{ $chartData['total_hours'] }} jam</span>
                            </p>
                        </div>
                        @if(!$chartData['has_planned_data'])
                        <div class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4" role="alert">
                            <p class="font-bold">Kurva Rencana Kosong</p>
                            <p>Tidak ada tugas dengan "Estimasi Jam" yang ditemukan di proyek ini. Kurva rencana tidak dapat dibuat.</p>
                        </div>
                        @endif
                        @if(!$chartData['has_actual_data'])
                        <div class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4" role="alert">
                            <p class="font-bold">Kurva Aktual Kosong</p>
                            <p>Tidak ada "Time Log" (catatan waktu kerja) yang ditemukan di proyek ini. Kurva aktual tidak dapat dibuat.</p>
                        </div>
                        @endif
                    </div>

                    <div class="mt-4 bg-white p-5 rounded-lg shadow-lg border border-gray-100">
                        <canvas id="sCurveChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const ctx = document.getElementById('sCurveChart');
            const chartData = @json($chartData);

            // Hancurkan instance chart yang ada sebelum membuat yang baru (penting untuk Livewire/Alpine.js atau navigasi SPA)
            const existingChart = Chart.getChart(ctx);
            if (existingChart) {
                existingChart.destroy();
            }

            // Pesan jika tidak ada data
            if (!chartData || !chartData.labels || chartData.labels.length === 0) {
                ctx.style.display = 'none';
                const parentDiv = ctx.parentElement;
                if (parentDiv && !parentDiv.querySelector('.chart-no-data-message')) {
                    const noDataMessage = document.createElement('p');
                    noDataMessage.className = 'chart-no-data-message text-center text-gray-500 py-10 text-lg';
                    noDataMessage.textContent = 'Tidak ada data aktivitas atau perencanaan untuk menampilkan Kurva S.';
                    parentDiv.appendChild(noDataMessage);
                }
                return;
            } else {
                ctx.style.display = 'block';
                const noDataMessage = ctx.parentElement.querySelector('.chart-no-data-message');
                if (noDataMessage) noDataMessage.remove();
            }


            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: chartData.labels,
                    datasets: [
                        {
                            label: 'Rencana Kumulatif (Jam)',
                            data: chartData.planned,
                            borderColor: 'rgb(99, 102, 241)', // indigo-500
                            backgroundColor: 'rgba(99, 102, 241, 0.1)', // Isi area di bawah garis
                            tension: 0.3, // Membuat garis sedikit melengkung
                            fill: true, // Mengisi area di bawah garis
                            pointBackgroundColor: 'rgb(99, 102, 241)',
                            pointBorderColor: '#fff',
                            pointHoverBackgroundColor: '#fff',
                            pointHoverBorderColor: 'rgb(99, 102, 241)',
                            pointRadius: 4,
                            pointHoverRadius: 6
                        },
                        {
                            label: 'Aktual Kumulatif (Jam)',
                            data: chartData.actual,
                            borderColor: 'rgb(34, 197, 94)', // green-500
                            backgroundColor: 'rgba(34, 197, 94, 0.1)', // Isi area di bawah garis
                            tension: 0.3,
                            fill: true,
                            pointBackgroundColor: 'rgb(34, 197, 94)',
                            pointBorderColor: '#fff',
                            pointHoverBackgroundColor: '#fff',
                            pointHoverBorderColor: 'rgb(34, 197, 94)',
                            pointRadius: 4,
                            pointHoverRadius: 6
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false, // Penting untuk mengontrol tinggi dengan CSS jika diperlukan
                    plugins: {
                        legend: {
                            position: 'bottom', // Pindahkan legend ke bawah
                            labels: {
                                font: {
                                    size: 14,
                                    family: 'Figtree' // Menggunakan font konsisten
                                },
                                color: '#374151' // Warna teks legend
                            }
                        },
                        title: {
                            display: true,
                            text: 'Perbandingan Progres Rencana vs Aktual',
                            font: {
                                size: 18,
                                weight: 'bold',
                                family: 'Figtree'
                            },
                            color: '#374151'
                        },
                        tooltip: { // Styling tooltip
                            mode: 'index',
                            intersect: false,
                            callbacks: {
                                label: function(context) {
                                    let label = context.dataset.label || '';
                                    if (label) {
                                        label += ': ';
                                    }
                                    if (context.parsed.y !== null) {
                                        label += context.parsed.y + ' Jam'; // Tambahkan 'Jam'
                                    }
                                    return label;
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: 'Akumulasi Jam Kerja',
                                font: {
                                    size: 14,
                                    family: 'Figtree'
                                },
                                color: '#4b5563'
                            },
                            ticks: {
                                font: {
                                    family: 'Figtree'
                                },
                                color: '#4b5563'
                            },
                            grid: {
                                color: '#e5e7eb' // Warna grid horizontal
                            }
                        },
                        x: {
                            title: {
                                display: true,
                                text: 'Tanggal',
                                font: {
                                    size: 14,
                                    family: 'Figtree'
                                },
                                color: '#4b5563'
                            },
                            ticks: {
                                font: {
                                    family: 'Figtree'
                                },
                                color: '#4b5563'
                            },
                            grid: {
                                display: false // Sembunyikan grid vertikal
                            }
                        }
                    }
                }
            });
        });
    </script>
    @endpush
</x-app-layout>