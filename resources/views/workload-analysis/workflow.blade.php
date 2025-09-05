<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            <i class="fas fa-chart-bar mr-2"></i>
            {{ __('Alur Kerja Modul Analisis Beban Kerja') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">

            <!-- Intro Card -->
            <x-card>
                <div class="p-6">
                    <h3 class="text-xl font-bold text-gray-800 mb-2">Dokumentasi Alur Kerja Analisis Beban Kerja</h3>
                    <p class="text-gray-600">Halaman ini berisi dokumentasi lengkap mengenai alur kerja Modul Analisis Beban Kerja, yang dirancang untuk para pimpinan (manajer) guna memantau dan mengelola beban kerja tim mereka.</p>
                </div>
            </x-card>

            <!-- Flowchart Umum -->
            <x-card>
                <div class="p-6">
                    <h3 class="text-xl font-bold text-gray-800 mb-4 border-b pb-2">Flowchart Alur Kerja</h3>
                    <p class="text-gray-600 mb-6">Flowchart ini merinci keseluruhan alur kerja standar untuk modul Analisis Beban Kerja.</p>
                    <div class="p-4 bg-gray-50 rounded-lg text-center">
                        <pre class="mermaid">
graph TD
    classDef page fill:#EBF5FB,stroke:#3498DB,color:#2874A6,stroke-width:1px;
    classDef action fill:#FEF9E7,stroke:#F1C40F,color:#B7950B,stroke-width:1px;
    classDef process fill:#E8F8F5,stroke:#1ABC9C,color:#148F77,stroke-width:1px;
    classDef decision fill:#FDEDEC,stroke:#C0392B,color:#A93226,stroke-width:1px;

    subgraph "A. Tampilan Utama (Untuk Pimpinan)"
        A1["<i class='fa fa-user-tie'></i> Pimpinan"]:::action -->|Akses Menu| A2["<i class='fa fa-list-alt'></i> Halaman Index Analisis<br>(workload-analysis.index)"]:::page;
        A2 -- Data ditampilkan --> A3["Tabel Daftar Bawahan &<br>Ringkasan Beban Kerja"]:::page;
        A3 -->|Klik Nama Pegawai| B_Flow["<i class='fa fa-eye'></i> Alur Detail Beban Kerja"];
        A3 -->|Ubah Penilaian Perilaku| C_Flow["<i class='fa fa-sliders-h'></i> Alur Update Perilaku"];
    end

    subgraph B_Flow [B. Alur Detail Beban Kerja Pegawai]
        B1[Klik Nama Pegawai] --> B2{<i class='fa fa-shield-alt'></i> Cek Izin: 'view' User}:::decision;
        B2 -- Diizinkan --> B3["<i class='fa fa-tasks'></i> Halaman Detail Beban Kerja<br>(workload-analysis.show)"]:::page;
        B2 -- Ditolak --> B4["<i class='fa fa-ban'></i> Akses Ditolak"];
        B3 -- Menampilkan --> B5["Daftar Tugas Kegiatan,<br>Tugas Harian, & SK Penugasan"];
    end

    subgraph C_Flow [C. Alur Update Penilaian Perilaku]
        C1[Ubah Pilihan Perilaku] --> C2["<i class='fa fa-cogs'></i> AJAX Call:<br>WorkloadAnalysisController@updateBehavior"]:::process;
        C2 --> C3{<i class='fa fa-check-double'></i> Validasi & Simpan}:::decision;
        C3 -- Sukses --> C4["<i class='fa fa-calculator'></i> PerformanceCalculatorService<br>Menghitung ulang skor kinerja"]:::process;
        C4 --> C5["<i class='fa fa-sync-alt'></i> Kirim balik data<br>terbaru via JSON"];
    end

                        </pre>
                    </div>
                </div>
            </x-card>

            <!-- Penjelasan Detail -->
            <x-card>
                <div class="p-6">
                    <h3 class="text-xl font-bold text-gray-800 mb-4 border-b pb-2">Penjelasan Detail Alur Kerja</h3>
                    <div class="prose max-w-none text-gray-700 space-y-4">
                        <div>
                            <h4 class="font-semibold text-gray-800">1. Halaman Utama Analisis (A)</h4>
                            <p>Halaman ini hanya dapat diakses oleh pengguna dengan peran pimpinan (manajer). Superadmin dapat melihat semua pengguna, sementara pimpinan lain hanya dapat melihat bawahan dalam hierarki unit kerjanya.</p>
                             <ul class="list-disc list-inside ml-4 space-y-2">
                                <li><strong>Tampilan</strong>: Menampilkan tabel berisi daftar bawahan. Setiap baris menunjukkan ringkasan beban kerja seperti total jam estimasi, jumlah tugas, dan skor kinerja.</li>
                                <li><strong>Grafik</strong>: Terdapat juga grafik yang memvisualisasikan distribusi total jam kerja di antara para bawahan.</li>
                             </ul>
                        </div>
                        <div>
                            <h4 class="font-semibold text-gray-800">2. Halaman Detail Beban Kerja (B)</h4>
                            <p>Dengan mengklik nama seorang pegawai, pimpinan dapat melihat rincian lengkap dari semua pekerjaan yang sedang ditugaskan kepada pegawai tersebut.</p>
                            <ul class="list-disc list-inside ml-4 space-y-2">
                                <li><strong>Rincian Pekerjaan</strong>: Halaman ini mengelompokkan dan menampilkan semua tugas dari kegiatan (proyek), tugas harian (ad-hoc), dan SK Penugasan yang sedang aktif untuk pegawai tersebut.</li>
                            </ul>
                        </div>
                        <div>
                            <h4 class="font-semibold text-gray-800">3. Update Penilaian Perilaku (C)</h4>
                            <p>Pimpinan dapat memberikan penilaian terhadap perilaku kerja bawahan langsung dari halaman daftar. Fitur ini sangat interaktif:</p>
                             <ul class="list-disc list-inside ml-4 space-y-2">
                                <li><strong>Aksi</strong>: Pimpinan mengubah pilihan pada dropdown penilaian (misal: dari 'Sesuai Ekspektasi' menjadi 'Diatas Ekspektasi').</li>
                                <li><strong>AJAX Call</strong>: Aksi ini secara otomatis memicu panggilan JavaScript (AJAX) ke server tanpa me-reload halaman.</li>
                                <li><strong>Kalkulasi Ulang</strong>: Di server, `PerformanceCalculatorService` akan langsung menghitung ulang skor kinerja pegawai (dan pimpinan yang menilai) berdasarkan perubahan tersebut. Data skor yang baru kemudian dikirim kembali ke halaman untuk diperbarui secara dinamis.</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </x-card>

        </div>
    </div>

    @push('scripts')
        <script type="module">
            import mermaid from 'https://cdn.jsdelivr.net/npm/mermaid@10/dist/mermaid.esm.min.mjs';
            mermaid.initialize({
                startOnLoad: true,
                fontFamily: 'inherit',
                theme: 'base',
                themeVariables: {
                    primaryColor: '#ffffff',
                    primaryTextColor: '#333',
                    primaryBorderColor: '#e5e7eb',
                    lineColor: '#6b7280',
                    textColor: '#374151',
                    fontSize: '14px',
                }
            });
        </script>
    @endpush
</x-app-layout>
