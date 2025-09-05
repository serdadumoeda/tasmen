<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            <i class="fas fa-sitemap mr-2"></i>
            {{ __('Alur Kerja Lengkap Modul Kegiatan') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">

            <x-card>
                <div class="p-6">
                    <h3 class="text-xl font-bold text-gray-800 mb-4 border-b pb-2">Flowchart Alur Kerja Kegiatan</h3>
                    <p class="text-gray-600 mb-6">Flowchart ini merinci keseluruhan alur kerja modul kegiatan, mulai dari navigasi awal, pembuatan kegiatan, hingga interaksi dengan semua fitur di halaman detail.</p>
                    <div class="p-4 bg-gray-50 rounded-lg text-center">
                        <pre class="mermaid">
graph TD
    %% --- Definisi Style ---
    classDef page fill:#EBF5FB,stroke:#3498DB,color:#2874A6,stroke-width:1px;
    classDef action fill:#FEF9E7,stroke:#F1C40F,color:#B7950B,stroke-width:1px;
    classDef process fill:#E8F8F5,stroke:#1ABC9C,color:#148F77,stroke-width:1px;
    classDef decision fill:#FDEDEC,stroke:#C0392B,color:#A93226,stroke-width:1px;
    classDef io fill:#F4ECF7,stroke:#8E44AD,color:#6C3483,stroke-width:1px;

    %% --- 1. Alur Utama ---
    subgraph "1. Alur Utama Navigasi"
        A["<i class='fa fa-desktop'></i> Dashboard"]:::io -->|Klik Menu 'Kegiatan'| B["<i class='fa fa-list-alt'></i> Halaman Daftar Kegiatan"]:::page;
        B -->|Klik Tombol<br><b>'Buat Kegiatan Baru'</b>| C_Flow["<i class='fa fa-plus-circle'></i> Alur Pembuatan Kegiatan"];
        B -->|Klik Nama Kegiatan| D_Flow["<i class='fa fa-eye'></i> Alur Detail Kegiatan"];
    end

    %% --- 2. Alur Pembuatan Kegiatan ---
    subgraph C_Flow [2. Alur Pembuatan Kegiatan Baru]
        C1[Mulai dari Daftar Kegiatan] --> C2{<i class='fa fa-shield-alt'></i> Cek Izin: 'create'}:::decision;
        C2 -- <i class='fa fa-check'></i> Diizinkan --> C3["<i class='fa fa-keyboard'></i> Form Step 1: Inisiasi"]:::page;
        C2 -- <i class='fa fa-times'></i> Ditolak --> C_End(<i class='fa fa-ban'></i> Akses Ditolak);
        C3 -- Isi data & Submit --> C4{<i class='fa fa-check-double'></i> Validasi Input}:::decision;
        C4 -- <i class='fa fa-times'></i> Gagal --> C3;
        C4 -- <i class='fa fa-check'></i> Sukses --> C5["<i class='fa fa-save'></i> Simpan Project"]:::process;
        C5 --> C6["<i class='fa fa-users'></i> Form Step 2: Tambah Tim"]:::page;
        C6 -- Isi Tim & Submit --> C7{<i class='fa fa-check-double'></i> Validasi Tim}:::decision;
        C7 -- <i class='fa fa-times'></i> Gagal --> C6;
        C7 -- <i class='fa fa-check'></i> Sukses --> C8["<i class='fa fa-sync-alt'></i> Update & Sinkronisasi Tim"]:::process;
        C8 --> D_Flow;
    end

    %% --- 3. Alur Detail Kegiatan ---
    subgraph D_Flow [3. Halaman Detail Kegiatan & Fitur-Fiturnya]
        D1["<i class='fa fa-file-alt'></i> Halaman Detail"]:::page --> D_Tugas[Tab: Ringkasan Tugas];
        D1 --> D_Tim[Tab: Tim];
        D1 --> D_Anggaran[Tab: Anggaran];
        D1 --> D_Surat[Tab: Surat Terkait];
        D1 --> D_Aksi[Area Tombol Aksi];
    end

    %% --- Sub-flows dari Detail Kegiatan ---
    subgraph D_Tugas [Tab: Ringkasan Tugas]
        T1[Daftar Tugas]:::page --> T2["<i class='fa fa-plus'></i> Aksi: Tambah Tugas"]:::action;
        T2 --> T3[Modal/Form Tambah Tugas]:::page;
        T3 -- Submit --> T4["<i class='fa fa-cogs'></i> Controller: TaskController@store"]:::process;
        T4 -- Sukses --> T1;
    end

    subgraph D_Aksi [Area Tombol Aksi]
        A1["<i class='fa fa-edit'></i> Tombol: Edit Kegiatan"]:::action --> A2["<i class='fa fa-cogs'></i> Controller: ProjectController@edit"]:::process;
        A2 --> A3["<i class='fa fa-arrow-right'></i> Halaman Edit Kegiatan"]:::page;

        A4["<i class='fa fa-th-large'></i> Tombol: Papan Kanban"]:::action --> A5["<i class='fa fa-cogs'></i> Controller: ProjectController@showKanban"]:::process;
        A5 --> A6["<i class='fa fa-arrow-right'></i> Halaman Kanban"]:::page;
        A6 -- Drag & Drop Tugas --> A7["<i class='fa fa-cogs'></i> AJAX Call: TaskController@updateStatus"]:::process;
        A7 --> A6;

        A8["<i class='fa fa-calendar-alt'></i> Tombol: Kalender"]:::action --> A9["<i class='fa fa-cogs'></i> Controller: ProjectController@showCalendar"]:::process;
        A9 --> A10["<i class='fa fa-arrow-right'></i> Halaman Kalender"]:::page;
        A10 -- Memuat data dari --> A11["<i class='fa fa-cogs'></i> Endpoint: ProjectController@tasksJson"]:::process;

        A12["<i class='fa fa-file-pdf'></i> Tombol: Laporan PDF"]:::action --> A13["<i class='fa fa-cogs'></i> Controller: ProjectController@downloadReport"]:::process;
        A13 --> A14["<i class='fa fa-download'></i> Generate & Unduh PDF"];
    end
                        </pre>
                    </div>
                </div>
            </x-card>

            <x-card>
                <div class="p-6">
                    <h3 class="text-xl font-bold text-gray-800 mb-4 border-b pb-2">Penjelasan Detail Alur Kerja</h3>
                    <div class="prose max-w-none text-gray-700 space-y-4">
                        <div>
                            <h4 class="font-semibold text-gray-800">1. Alur Utama Navigasi</h4>
                            <p>Pengguna memulai dari Dashboard, lalu masuk ke menu utama Kegiatan untuk melihat daftar semua kegiatan yang dapat diakses. Dari daftar ini, pengguna dapat memilih untuk membuat kegiatan baru atau melihat detail kegiatan yang sudah ada.</p>
                        </div>
                        <div>
                            <h4 class="font-semibold text-gray-800">2. Alur Pembuatan Kegiatan Baru</h4>
                            <p>Ini adalah proses dua langkah yang terstruktur. Pertama, sistem akan memvalidasi izin pengguna. Kemudian, pengguna mengisi detail dasar kegiatan. Setelah validasi berhasil, pengguna melanjutkan ke langkah kedua untuk membentuk tim dan menunjuk ketua. Jika semua validasi sukses, kegiatan baru berhasil dibuat dan pengguna diarahkan ke halaman detailnya.</p>
                        </div>
                        <div>
                            <h4 class="font-semibold text-gray-800">3. Halaman Detail Kegiatan & Fitur-Fiturnya</h4>
                            <p>Ini adalah halaman pusat untuk mengelola sebuah kegiatan. Semua fitur utama dapat diakses dari sini:</p>
                            <ul class="list-disc list-inside ml-4 space-y-2">
                                <li><strong>Manajemen Tugas:</strong> Pengguna dapat menambah tugas baru melalui form modal, yang datanya akan disimpan melalui `TaskController`. Daftar tugas akan otomatis diperbarui.</li>
                                <li><strong>Tombol Aksi:</strong>
                                    <ul class="list-disc list-inside ml-6">
                                        <li><strong>Edit Kegiatan:</strong> Membuka halaman form baru untuk mengedit data inti proyek.</li>
                                        <li><strong>Papan Kanban:</strong> Menyediakan tampilan visual untuk manajemen status tugas secara interaktif melalui panggilan AJAX.</li>
                                        <li><strong>Kalender:</strong> Menampilkan jadwal dan deadline tugas dengan memuat data dari endpoint JSON.</li>
                                        <li><strong>Laporan PDF:</strong> Memicu proses di server untuk men-generate laporan ringkasan proyek yang bisa langsung diunduh.</li>
                                    </ul>
                                </li>
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
