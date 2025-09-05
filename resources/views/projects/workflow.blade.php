<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            <i class="fas fa-sitemap mr-2"></i>
            {{ __('Alur Kerja Detail Modul Kegiatan') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">

            <!-- Definisi Style untuk Mermaid -->
            <style>
                .mermaid .page { fill: #EBF5FB; stroke: #3498DB; stroke-width: 1px; }
                .mermaid .action { fill: #FEF9E7; stroke: #F1C40F; stroke-width: 1px; }
                .mermaid .process { fill: #E8F8F5; stroke: #1ABC9C; stroke-width: 1px; }
                .mermaid .fa { font-family: 'Font Awesome 6 Free'; font-weight: 900; }
            </style>

            <!-- Alur Detail Kegiatan -->
            <x-card>
                <div class="p-6">
                    <h3 class="text-xl font-bold text-gray-800 mb-4 border-b pb-2">Halaman Detail Kegiatan & Fitur-Fiturnya</h3>
                    <p class="text-gray-600 mb-6">Flowchart ini merinci semua interaksi dan proses yang terjadi di dalam halaman detail sebuah kegiatan, yang merupakan pusat dari semua aktivitas.</p>
                    <div class="p-4 bg-gray-50 rounded-lg text-center">
                        <pre class="mermaid">
graph TD
    subgraph "Halaman Detail Kegiatan (projects.show)"
        A["<i class='fa fa-file-alt'></i> Halaman Detail"]:::page --> B[Tab: Ringkasan Tugas];
        A --> C[Tab: Tim];
        A --> D[Tab: Anggaran];
        A --> E[Tab: Surat Terkait];
        A --> F[Area Tombol Aksi];
    end

    subgraph B [Tab: Ringkasan Tugas]
        B1[Daftar Tugas]:::page --> B2["<i class='fa fa-plus'></i> Aksi: Tambah Tugas"]:::action;
        B2 --> B3[Modal/Form Tambah Tugas]:::page;
        B3 -- Submit --> B4["<i class='fa fa-cogs'></i> Controller: TaskController@store"]:::process;
        B4 -- Sukses --> B1;

        B1 --> B5["<i class='fa fa-edit'></i> Aksi: Edit Tugas"]:::action;
        B5 --> B6["<i class='fa fa-arrow-right'></i> Halaman Edit Tugas (tasks.edit)"]:::page;

        B1 --> B7["<i class='fa fa-filter'></i> Aksi: Filter & Urutkan"]:::action;
        B7 -- Submit --> B8["<i class='fa fa-cogs'></i> Controller: ProjectController@show (Reload)"]:::process;
        B8 --> B1;
    end

    subgraph F [Area Tombol Aksi]
        F1["<i class='fa fa-edit'></i> Tombol: Edit Kegiatan"]:::action --> F2["<i class='fa fa-cogs'></i> Controller: ProjectController@edit"]:::process;
        F2 --> F3["<i class='fa fa-arrow-right'></i> Halaman Edit Kegiatan (projects.edit)"]:::page;

        F4["<i class='fa fa-th-large'></i> Tombol: Papan Kanban"]:::action --> F5["<i class='fa fa-cogs'></i> Controller: ProjectController@showKanban"]:::process;
        F5 --> F6["<i class='fa fa-arrow-right'></i> Halaman Kanban (projects.kanban)"]:::page;
        F6 -- Drag & Drop Tugas --> F7["<i class='fa fa-cogs'></i> AJAX Call: TaskController@updateStatus"]:::process;
        F7 --> F6;

        F8["<i class='fa fa-calendar-alt'></i> Tombol: Kalender"]:::action --> F9["<i class='fa fa-cogs'></i> Controller: ProjectController@showCalendar"]:::process;
        F9 --> F10["<i class='fa fa-arrow-right'></i> Halaman Kalender (projects.calendar)"]:::page;
        F10 -- Memuat data dari --> F11["<i class='fa fa-cogs'></i> Endpoint: ProjectController@tasksJson"]:::process;

        F12["<i class='fa fa-chart-line'></i> Tombol: Kurva S"]:::action --> F13["<i class='fa fa-cogs'></i> Controller: ProjectController@sCurve"]:::process;
        F13 --> F14["<i class='fa fa-arrow-right'></i> Halaman Grafik Kurva S (projects.s-curve)"]:::page;

        F15["<i class='fa fa-file-pdf'></i> Tombol: Laporan PDF"]:::action --> F16["<i class='fa fa-cogs'></i> Controller: ProjectController@downloadReport"]:::process;
        F16 --> F17["<i class='fa fa-download'></i> Generate & Unduh PDF"];
    end

    subgraph C [Tab: Tim]
        C1[Lihat Anggota Tim]:::page --> C2[Lihat Analisis Beban Kerja]:::page;
        C1 -- Ditangani oleh --> C3["<i class='fa fa-cogs'></i> Controller: ProjectController@teamDashboard"]:::process;
    end

    subgraph D [Tab: Anggaran]
        D1[Lihat Anggaran]:::page --> D2["<i class='fa fa-plus'></i> Aksi: Tambah/Edit Item"]:::action;
        D1 --> D3["<i class='fa fa-dollar-sign'></i> Aksi: Tambah Realisasi"]:::action;
        D1 -- Ditangani oleh --> D4["<i class='fa fa-cogs'></i> Controller: BudgetItemController"]:::process;
    end

    classDef page fill:#EBF5FB,stroke:#3498DB,color:#2874A6;
    classDef action fill:#FEF9E7,stroke:#F1C40F,color:#B7950B;
    classDef process fill:#E8F8F5,stroke:#1ABC9C,color:#148F77;
                        </pre>
                    </div>
                </div>
            </x-card>

            <x-card>
                <div class="p-6">
                    <h3 class="text-xl font-bold text-gray-800 mb-4 border-b pb-2">Penjelasan Sangat Detail untuk Setiap Fitur</h3>
                    <div class="prose max-w-none text-gray-700">
                        <h4 class="font-semibold text-gray-800">1. Tab: Ringkasan Tugas (B)</h4>
                        <ul class="list-disc list-inside space-y-2">
                            <li><strong>Tampilan (B1):</strong> Menampilkan tabel daftar tugas yang terkait dengan kegiatan ini. Kolom yang ada biasanya: Judul Tugas, Prioritas, Status, Tanggal Deadline, dan Penanggung Jawab (Assignees).</li>
                            <li><strong>Tambah Tugas (B2-B4):</strong>
                                <ul class="list-disc list-inside ml-4">
                                    <li>Ketika tombol 'Tambah Tugas' diklik, sebuah form (kemungkinan dalam bentuk modal/popup) akan muncul.</li>
                                    <li>Form ini berisi field seperti: Judul, Deskripsi, Tanggal Deadline, Prioritas, dan pilihan Anggota Tim untuk ditugaskan.</li>
                                    <li>Setelah disubmit, data dikirim ke method <code>store</code> di <code>TaskController</code>. Method ini akan melakukan validasi, menyimpan tugas baru ke database, dan menautkannya dengan kegiatan ini. Halaman akan dimuat ulang untuk menampilkan tugas baru di daftar.</li>
                                </ul>
                            </li>
                            <li><strong>Filter & Urutkan (B7-B8):</strong> Pengguna bisa memfilter daftar tugas (misalnya hanya menampilkan yang berstatus "In Progress") atau mengurutkannya (misalnya berdasarkan deadline). Aksi ini akan me-reload halaman detail kegiatan dengan parameter query baru.</li>
                        </ul>

                        <h4 class="font-semibold text-gray-800 mt-6">2. Tombol Aksi Utama (F)</h4>
                        <ul class="list-disc list-inside space-y-2">
                            <li><strong>Edit Kegiatan (F1-F3):</strong> Tombol ini mengarahkan pengguna ke halaman baru (<code>projects.edit</code>) yang berisi form untuk mengubah data inti kegiatan seperti nama, deskripsi, tanggal, dan komposisi tim. Proses ini ditangani oleh <code>ProjectController@edit</code> (untuk menampilkan form) dan <code>ProjectController@update</code> (untuk menyimpan perubahan).</li>
                            <li><strong>Papan Kanban (F4-F7):</strong> Mengarahkan ke halaman visual (<code>projects.kanban</code>) di mana tugas-tugas ditampilkan sebagai kartu dalam kolom-kolom status (Pending, In Progress, Selesai). Pengguna bisa menggeser kartu dari satu kolom ke kolom lain (drag & drop). Aksi ini memicu panggilan <strong>AJAX</strong> di latar belakang ke <code>TaskController@updateStatus</code> untuk mengubah status tugas secara instan tanpa me-reload seluruh halaman.</li>
                            <li><strong>Kalender (F8-F11):</strong> Membuka halaman kalender (<code>projects.calendar</code>) yang menampilkan deadline tugas. Kalender ini secara dinamis memuat data tugas dari sebuah endpoint API (<code>ProjectController@tasksJson</code>) yang mengembalikan data dalam format JSON.</li>
                            <li><strong>Kurva S (F12-F14):</strong> Fitur analisis canggih yang mengarahkan ke halaman grafik (<code>projects.s-curve</code>). Halaman ini membandingkan rencana kumulatif (berdasarkan estimasi jam) dengan progres aktual (berdasarkan <em>time log</em> yang diisi oleh anggota tim).</li>
                            <li><strong>Laporan PDF (F15-F17):</strong> Aksi ini tidak membuka halaman baru, tetapi langsung memicu method <code>ProjectController@downloadReport</code> di server. Method ini akan men-generate file laporan dalam format PDF dan mengirimkannya ke browser pengguna untuk diunduh.</li>
                        </ul>

                        <h4 class="font-semibold text-gray-800 mt-6">3. Tab Lainnya (C, D, E)</h4>
                        <ul class="list-disc list-inside space-y-2">
                            <li><strong>Tab Tim (C):</strong> Menampilkan daftar anggota tim beserta analisis beban kerja mereka (misalnya, jumlah tugas yang sedang dikerjakan, total estimasi jam). Ditangani oleh <code>ProjectController@teamDashboard</code>.</li>
                            <li><strong>Tab Anggaran (D):</strong> Area khusus untuk manajemen finansial kegiatan. Pengguna bisa mendefinisikan pos-pos anggaran dan mencatat setiap realisasi (pengeluaran) yang terjadi. Ditangani oleh <code>BudgetItemController</code>.</li>
                            <li><strong>Tab Surat Terkait (E):</strong> Menampilkan daftar surat-surat yang menjadi dasar atau referensi dari kegiatan ini. Ini adalah hasil dari relasi polimorfik yang sudah kita bahas sebelumnya.</li>
                        </ul>
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
