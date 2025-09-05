<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Alur Kerja Modul Kegiatan') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">

                    <h3 class="text-lg font-medium text-gray-900 mb-4">Flowchart Visual</h3>
                    <div class="p-4 bg-gray-50 rounded-lg">
                        <pre class="mermaid">
graph TD
    %% --- Main Flow ---
    A[Start: Dashboard] --> B{Akses Menu 'Kegiatan'};
    B --> C[Halaman Daftar Kegiatan];
    C -->|Klik 'Buat Kegiatan'| D_Flow[Alur Pembuatan Kegiatan Baru];
    C -->|Klik nama kegiatan| E_Flow[Alur Lihat Detail Kegiatan];

    %% --- D: Alur Pembuatan Kegiatan Baru ---
    subgraph D_Flow [Alur Pembuatan Kegiatan Baru]
        D1[Klik 'Buat Kegiatan'] --> D2{Cek Otorisasi: 'create' Project};
        D2 -- Diizinkan --> D3[Tampilkan Form Step 1: Inisiasi];
        D2 -- Ditolak --> D_End(End: Akses Ditolak);
        D3 -- Isi data & submit --> D4{Proses: Validasi Input Step 1};
        D4 -- Gagal --> D3;
        D4 -- Sukses --> D5[Simpan data Project & Owner];
        D5 --> D6[Redirect ke Form Step 2: Tambah Tim];
        D6 -- Isi data tim & submit --> D7{Proses: Validasi Input Step 2};
        D7 -- Gagal --> D6;
        D7 -- Sukses --> D8[Update Project Leader & sinkronisasi Anggota Tim];
        D8 --> E_Flow;
    end

    %% --- E: Alur Lihat Detail Kegiatan ---
    subgraph E_Flow [Alur Lihat Detail Kegiatan]
        E1[Klik Nama Kegiatan] --> E2{Cek Otorisasi: 'view' Project};
        E2 -- Diizinkan --> E3[Tampilkan Halaman Detail Kegiatan];
        E2 -- Ditolak --> E_End(End: Akses Ditolak);
        E3 --> E_Tugas[Tab: Tugas];
        E3 --> E_Tim[Tab: Tim];
        E3 --> E_Anggaran[Tab: Anggaran];
        E3 --> E_Lainnya[Fitur Lainnya];
    end

    %% --- Sub-Flows dari Detail Kegiatan ---
    subgraph E_Tugas [Tab Tugas]
        T1[Lihat Daftar Tugas] --> T2[Klik 'Tambah Tugas'];
        T2 --> T3{Proses: Tampilkan Modal/Form Tambah Tugas};
        T3 -- Isi data & submit --> T4{Proses: Validasi & Simpan Tugas Baru};
        T4 --> T1;
        T1 -->|Klik 'Edit Tugas'| T5[Alur Edit Tugas];
        T5 --> T1;
    end

    subgraph E_Lainnya [Fitur Lainnya]
        L1[Tombol 'Edit Kegiatan'] --> L2{Cek Otorisasi: 'update' Project};
        L2 -- Diizinkan --> L3[Tampilkan Form Edit Kegiatan];
        L3 -- Submit perubahan --> L4{Proses: Validasi & Update Kegiatan};
        L4 --> E3;

        L5[Tombol 'Papan Kanban'] --> L6[Tampilkan Halaman Kanban];
        L6 -->|Drag & Drop Tugas| L7{Proses: Update Status Tugas};
        L7 --> L6;

        L8[Tombol 'Kalender'] --> L9[Tampilkan Halaman Kalender];
        L10[Tombol 'Kurva S'] --> L11[Tampilkan Halaman Kurva S];
        L12[Tombol 'Laporan PDF'] --> L13{Proses: Generate & Download Laporan PDF};
    end
                        </pre>
                    </div>

                    <h3 class="text-lg font-medium text-gray-900 mt-8 mb-4">Penjelasan Rinci Alur Proses</h3>
                    <div class="prose max-w-none">
                        <ol>
                            <li>
                                <strong>Akses Awal:</strong>
                                <ul>
                                    <li>Pengguna memulai dari <strong>Dashboard</strong> dan mengakses menu <strong>Kegiatan</strong>.</li>
                                    <li>Sistem menampilkan <strong>Halaman Daftar Kegiatan</strong>, di mana pengguna hanya bisa melihat kegiatan yang sesuai dengan hierarki jabatannya.</li>
                                </ul>
                            </li>
                            <li>
                                <strong>Alur Pembuatan Kegiatan Baru (Detail):</strong>
                                <ul>
                                    <li><strong>Otorisasi:</strong> Sebelum menampilkan form, sistem memeriksa apakah pengguna memiliki izin untuk <code>create</code> (membuat) kegiatan.</li>
                                    <li><strong>Step 1 (Inisiasi):</strong> Pengguna mengisi form. Saat submit, sistem melakukan <strong>validasi input</strong>. Jika gagal, pengguna dikembalikan ke form dengan pesan error. Jika berhasil, data dasar proyek disimpan.</li>
                                    <li><strong>Step 2 (Tim):</strong> Pengguna diarahkan ke form kedua untuk menambah tim. Sistem kembali melakukan <strong>validasi</strong> saat form ini di-submit. Jika berhasil, data ketua dan anggota tim disimpan, dan pengguna langsung diarahkan ke <strong>Halaman Detail Kegiatan</strong> yang baru dibuat.</li>
                                </ul>
                            </li>
                            <li>
                                <strong>Halaman Detail Kegiatan (Pusat Aktivitas):</strong>
                                <ul>
                                    <li><strong>Otorisasi:</strong> Sistem memeriksa izin <code>view</code> (melihat) sebelum menampilkan halaman detail.</li>
                                    <li>Halaman ini adalah pusat dari semua fitur terkait kegiatan.</li>
                                </ul>
                            </li>
                            <li>
                                <strong>Sub-Alur pada Tab Tugas:</strong>
                                <ul>
                                    <li>Saat pengguna mengklik <strong>'Tambah Tugas'</strong>, sebuah form akan muncul.</li>
                                    <li>Setelah disubmit, data tugas divalidasi dan disimpan. Halaman kemudian <strong>me-refresh daftar tugas</strong> untuk menampilkan tugas yang baru ditambahkan. Alurnya kembali ke daftar tugas.</li>
                                </ul>
                            </li>
                            <li>
                                <strong>Sub-Alur pada Fitur Lainnya:</strong>
                                <ul>
                                    <li><strong>Edit Kegiatan:</strong> Memerlukan izin <code>update</code>. Alurnya mirip dengan pembuatan kegiatan, yaitu menampilkan form, validasi, dan kembali ke halaman detail setelah berhasil.</li>
                                    <li><strong>Papan Kanban:</strong> Ini adalah fitur interaktif. Saat pengguna menggeser (<em>drag & drop</em>) sebuah kartu tugas, sistem memproses <strong>perubahan status tugas</strong> di latar belakang dan me-refresh tampilan papan Kanban.</li>
                                    <li><strong>Laporan PDF:</strong> Fitur ini memicu proses di server untuk men-generate file PDF dan langsung menawarkan unduhan ke pengguna.</li>
                                </ul>
                            </li>
                        </ol>
                    </div>

                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script type="module">
            import mermaid from 'https://cdn.jsdelivr.net/npm/mermaid@10/dist/mermaid.esm.min.mjs';
            mermaid.initialize({ startOnLoad: true });
        </script>
    @endpush
</x-app-layout>
