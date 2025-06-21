<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Kurva S: {{ $project->name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <a href="{{ route('projects.show', $project) }}" class="text-blue-600 hover:text-blue-800 font-medium mb-4 inline-block">&larr; Kembali ke Detail Proyek</a>
                    <p class="text-sm text-gray-600">Grafik ini membandingkan akumulasi jam kerja yang direncanakan (biru) dengan jam kerja aktual yang tercatat (hijau). Total jam yang direncanakan untuk proyek ini adalah **{{ $chartData['total_hours'] }} jam**.</p>
                    <div class="mt-4">
                        <canvas id="sCurveChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const ctx = document.getElementById('sCurveChart');
            const chartData = @json($chartData);

            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: chartData.labels,
                    datasets: [
                        {
                            label: 'Rencana Kumulatif (Jam)',
                            data: chartData.planned,
                            borderColor: 'rgb(59, 130, 246)',
                            backgroundColor: 'rgba(59, 130, 246, 0.5)',
                            tension: 0.1,
                            fill: false,
                        },
                        {
                            label: 'Aktual Kumulatif (Jam)',
                            data: chartData.actual,
                            borderColor: 'rgb(34, 197, 94)',
                            backgroundColor: 'rgba(34, 197, 94, 0.5)',
                            tension: 0.1,
                            fill: false,
                        }
                    ]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'top',
                        },
                        title: {
                            display: true,
                            text: 'Perbandingan Progres Rencana vs Aktual'
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: 'Akumulasi Jam Kerja'
                            }
                        }
                    }
                }
            });
        });
    </script>
</x-app-layout>