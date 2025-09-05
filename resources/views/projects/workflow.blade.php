<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            <i class="fas fa-sitemap mr-2"></i>
            {{ __('Alur Kerja Modul Kegiatan') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">

            <!-- Definisi Style untuk Mermaid -->
            <style>
                .mermaid .actor { fill: #D6EAF8; stroke: #2E86C1; }
                .mermaid .process { fill: #D1F2EB; stroke: #16A085; }
                .mermaid .decision { fill: #FDEDEC; stroke: #C0392B; }
                .mermaid .io { fill: #FCF3CF; stroke: #F39C12; }
            </style>

            <!-- Alur Utama -->
            <x-card>
                <div class="p-6">
                    <h3 class="text-xl font-bold text-gray-800 mb-4 border-b pb-2">1. Alur Utama Navigasi</h3>
                    <p class="text-gray-600 mb-6">Alur ini menunjukkan bagaimana pengguna berpindah dari halaman utama ke fitur-fitur inti dalam modul kegiatan.</p>
                    <div class="p-4 bg-gray-50 rounded-lg text-center">
                        <pre class="mermaid">
graph TD
    A[<i class='fas fa-desktop'></i> Dashboard]:::io -->|Klik Menu 'Kegiatan'| B[<i class='fas fa-list-alt'></i> Halaman Daftar Kegiatan]:::io;
    B -->|Klik Tombol<br><b>'Buat Kegiatan Baru'</b>| C[<i class='fas fa-plus-circle'></i> Alur Pembuatan Kegiatan];
    B -->|Klik Nama Kegiatan| D[<i class='fas fa-eye'></i> Alur Detail Kegiatan];

    classDef io fill:#EBF5FB,stroke:#3498DB,stroke-width:2px,color:#2874A6;
    classDef process fill:#E8F8F5,stroke:#1ABC9C,stroke-width:2px,color:#148F77;
    class A,B,C,D io;
                        </pre>
                    </div>
                </div>
            </x-card>

            <!-- Alur Pembuatan Kegiatan -->
            <x-card>
                <div class="p-6">
                    <h3 class="text-xl font-bold text-gray-800 mb-4 border-b pb-2">2. Alur Pembuatan Kegiatan Baru</h3>
                    <p class="text-gray-600 mb-6">Proses dua langkah untuk menginisiasi sebuah kegiatan baru, mulai dari pengisian data dasar hingga pembentukan tim.</p>
                    <div class="p-4 bg-gray-50 rounded-lg text-center">
                        <pre class="mermaid">
graph TD
    D1[<i class='fas fa-mouse-pointer'></i> Mulai] --> D2{<i class='fas fa-shield-alt'></i> Cek Izin: 'create'}:::decision;
    D2 -- <i class='fas fa-check'></i> Diizinkan --> D3[<i class='fas fa-keyboard'></i> Form Step 1: Inisiasi]:::io;
    D2 -- <i class='fas fa-times'></i> Ditolak --> D_End(<i class='fas fa-ban'></i> Akses Ditolak);
    D3 -- Isi data & Submit --> D4{<i class='fas fa-check-double'></i> Validasi Input}:::decision;
    D4 -- <i class='fas fa-times'></i> Gagal --> D3;
    D4 -- <i class='fas fa-check'></i> Sukses --> D5[<i class='fas fa-save'></i> Simpan Project]:::process;
    D5 --> D6[<i class='fas fa-users'></i> Form Step 2: Tambah Tim]:::io;
    D6 -- Isi Tim & Submit --> D7{<i class='fas fa-check-double'></i> Validasi Tim}:::decision;
    D7 -- <i class='fas fa-times'></i> Gagal --> D6;
    D7 -- <i class='fas fa-check'></i> Sukses --> D8[<i class='fas fa-sync-alt'></i> Update & Sinkronisasi Tim]:::process;
    D8 --> D9[<i class='fas fa-file-alt'></i> Redirect ke Halaman Detail];

    classDef io fill:#EBF5FB,stroke:#3498DB,stroke-width:2px,color:#2874A6;
    classDef process fill:#E8F8F5,stroke:#1ABC9C,stroke-width:2px,color:#148F77;
    classDef decision fill:#FEF9E7,stroke:#F1C40F,stroke-width:2px,color:#B7950B;
    class D1,D3,D6,D9 io;
    class D5,D8 process;
    class D2,D4,D7 decision;
                        </pre>
                    </div>
                </div>
            </x-card>

            <!-- Alur Detail Kegiatan -->
            <x-card>
                <div class="p-6">
                    <h3 class="text-xl font-bold text-gray-800 mb-4 border-b pb-2">3. Halaman Detail Kegiatan & Fitur-Fiturnya</h3>
                    <p class="text-gray-600 mb-6">Pusat dari semua aktivitas terkait sebuah kegiatan. Dari sini, pengguna dapat mengakses berbagai fitur manajemen.</p>
                    <div class="p-4 bg-gray-50 rounded-lg text-center">
                        <pre class="mermaid">
graph TD
    E1[<i class='fas fa-file-alt'></i> Halaman Detail Kegiatan]:::io --> E_Tugas[<i class='fas fa-tasks'></i> Tab: Tugas];
    E1 --> E_Tim[<i class='fas fa-users-cog'></i> Tab: Tim];
    E1 --> E_Anggaran[<i class='fas fa-wallet'></i> Tab: Anggaran];
    E1 --> E_Lainnya[<i class='fas fa-cogs'></i> Fitur Lainnya];

    subgraph E_Lainnya [Fitur Lainnya]
        direction LR
        L1[<i class='fas fa-edit'></i> Edit Kegiatan] --> L2{<i class='fas fa-shield-alt'></i> Cek Izin 'update'}:::decision;
        L5[<i class='fas fa-th-large'></i> Papan Kanban] --> L6[<i class='fas fa-chalkboard'></i> Halaman Kanban];
        L8[<i class='fas fa-calendar-alt'></i> Kalender] --> L9[<i class='fas fa-calendar-day'></i> Halaman Kalender];
        L10[<i class='fas fa-chart-line'></i> Kurva S] --> L11[<i class='fas fa-chart-area'></i> Halaman Kurva S];
        L12[<i class='fas fa-file-pdf'></i> Laporan PDF] --> L13[<i class='fas fa-download'></i> Generate & Unduh];
    end

    classDef io fill:#EBF5FB,stroke:#3498DB,stroke-width:2px,color:#2874A6;
    classDef decision fill:#FEF9E7,stroke:#F1C40F,stroke-width:2px,color:#B7950B;
    class E1 io;
    class L2 decision;
                        </pre>
                    </div>
                </div>
            </x-card>

        </div>
    </div>

    @push('scripts')
        <script type="module">
            import mermaid from 'https://cdn.jsdelivr.net/npm/mermaid@10/dist/mermaid.esm.min.mjs';
            // Add Font Awesome icons to Mermaid config
            mermaid.initialize({
                startOnLoad: true,
                fontFamily: 'inherit',
                theme: 'base',
                themeVariables: {
                    primaryColor: '#F8F9FA',
                    primaryTextColor: '#333',
                    primaryBorderColor: '#DEE2E6',
                    lineColor: '#6C757D',
                    textColor: '#333',
                }
            });
        </script>
    @endpush
</x-app-layout>
