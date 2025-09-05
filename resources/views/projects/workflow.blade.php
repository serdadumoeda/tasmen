<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            <i class="fas fa-sitemap mr-2"></i>
            {{ __('Alur Kerja Lengkap Modul Kegiatan') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">

            <!-- Intro Card -->
            <x-card>
                <div class="p-6">
                    <h3 class="text-xl font-bold text-gray-800 mb-2">Dokumentasi Alur Kerja</h3>
                    <p class="text-gray-600">Halaman ini berisi dokumentasi lengkap dan detail mengenai alur kerja Modul Kegiatan, dari navigasi umum hingga fitur-fitur spesifik. Gunakan ini sebagai panduan untuk memahami cara kerja sistem.</p>
                </div>
            </x-card>

            <!-- Flowchart Umum -->
            <x-card>
                <div class="p-6">
                    <h3 class="text-xl font-bold text-gray-800 mb-4 border-b pb-2">1. Flowchart Alur Kerja Umum</h3>
                    <p class="text-gray-600 mb-6">Flowchart ini merinci keseluruhan alur kerja modul kegiatan, mulai dari navigasi awal, pembuatan kegiatan, hingga interaksi dengan semua fitur di halaman detail.</p>
                    <div class="p-4 bg-gray-50 rounded-lg text-center">
                        <pre class="mermaid">
graph TD
    classDef page fill:#EBF5FB,stroke:#3498DB,color:#2874A6,stroke-width:1px;
    classDef action fill:#FEF9E7,stroke:#F1C40F,color:#B7950B,stroke-width:1px;
    classDef process fill:#E8F8F5,stroke:#1ABC9C,color:#148F77,stroke-width:1px;
    classDef decision fill:#FDEDEC,stroke:#C0392B,color:#A93226,stroke-width:1px;

    subgraph "A. Alur Utama Navigasi"
        A1["<i class='fa fa-desktop'></i> Dashboard"]:::page -->|Klik Menu| A2["<i class='fa fa-list-alt'></i> Halaman Daftar Kegiatan"]:::page;
        A2 -->|Klik 'Buat'| B_Flow["<i class='fa fa-plus-circle'></i> Alur Pembuatan Kegiatan"];
        A2 -->|Klik Nama Kegiatan| C_Flow["<i class='fa fa-eye'></i> Alur Detail Kegiatan"];
    end

    subgraph B_Flow [B. Alur Pembuatan Kegiatan Baru]
        B1[Mulai] --> B2{<i class='fa fa-shield-alt'></i> Cek Izin: 'create'}:::decision;
        B2 -- Diizinkan --> B3["<i class='fa fa-keyboard'></i> Form Step 1: Inisiasi"]:::page;
        B3 -- Submit --> B4{<i class='fa fa-check-double'></i> Validasi}:::decision;
        B4 -- Gagal --> B3;
        B4 -- Sukses --> B5["<i class='fa fa-save'></i> Simpan Project"]:::process;
        B5 --> B6["<i class='fa fa-users'></i> Form Step 2: Tim"]:::page;
        B6 -- Submit --> B7{<i class='fa fa-check-double'></i> Validasi}:::decision;
        B7 -- Gagal --> B6;
        B7 -- Sukses --> B8["<i class='fa fa-sync-alt'></i> Simpan Tim"]:::process;
        B8 --> C_Flow;
    end

    subgraph C_Flow [C. Alur Detail Kegiatan]
        C1["<i class='fa fa-file-alt'></i> Halaman Detail"]:::page --> C2[Tab & Tombol Aksi];
    end
                        </pre>
                    </div>
                </div>
            </x-card>

            <!-- Rincian Halaman Detail -->
            <x-card>
                <div class="p-6">
                    <h3 class="text-xl font-bold text-gray-800 mb-4 border-b pb-2">2. Rincian Halaman Detail Kegiatan</h3>
                    <p class="text-gray-600 mb-6">Halaman ini adalah pusat kendali untuk sebuah kegiatan. Strukturnya dibagi menjadi dua bagian utama: **Tombol Aksi Global** di header, dan **Area Konten Berbasis Tab**.</p>

                    <div class="prose max-w-none text-gray-700">
                        <h4 class="font-semibold text-gray-800">Tombol Aksi Global (di Header)</h4>
                        <ul class="list-disc list-inside space-y-2">
                            <li><strong>Dropdown "Tampilan & Laporan"</strong>: Menyediakan akses cepat ke berbagai visualisasi data (Kanban, Kalender, Kurva S) dan untuk mengunduh Laporan PDF.</li>
                            <li><strong>Tombol "Anggaran"</strong>: Mengarahkan ke halaman manajemen finansial kegiatan (memerlukan izin `update`).</li>
                            <li><strong>Tombol "Edit Kegiatan"</strong>: Mengarahkan ke halaman untuk mengubah data inti kegiatan (memerlukan izin `update`).</li>
                        </ul>

                        <h4 class="font-semibold text-gray-800 mt-6">Area Konten Berbasis Tab</h4>
                        <ul class="list-disc list-inside space-y-2">
                            <li><strong>Tab "Daftar Tugas" (Default)</strong>: Menampilkan semua tugas dalam kegiatan. Ini adalah area kerja utama.</li>
                            <li><strong>Tab "Informasi & Aktivitas"</strong>: Menampilkan deskripsi, detail, tim, dan log aktivitas proyek.</li>
                            <li><strong>Tab "Persuratan"</strong>: Menampilkan surat-surat yang terkait dengan kegiatan ini.</li>
                            <li><strong>Tab "Tambah Tugas Baru"</strong>: Form untuk menambah tugas baru (memerlukan izin `update`).</li>
                        </ul>
                    </div>
                </div>
            </x-card>

            <!-- Flowchart Fokus Tugas -->
            <x-card>
                <div class="p-6">
                    <h3 class="text-xl font-bold text-gray-800 mb-4 border-b pb-2">3. Flowchart Fokus: Manajemen Tugas</h3>
                    <p class="text-gray-600 mb-6">Flowchart ini merinci semua aksi yang bisa dilakukan pengguna terkait **Tugas** dari dalam Halaman Detail Kegiatan.</p>
                    <div class="p-4 bg-gray-50 rounded-lg text-center">
                        <pre class="mermaid">
graph TD
    subgraph "Alur Kerja Tab Tugas"
        A["<i class='fa fa-list-check'></i> Tab: Daftar Tugas"]:::page --> B["<i class='fa fa-filter'></i> Filter & Urutkan Tugas"]:::action;
        B -- Terapkan Filter --> A;

        A --> C["<i class='fa fa-plus-circle'></i> Aksi: Tambah Tugas Baru"]:::action;
        C -- Buka Tab/Form --> D[Form Tambah Tugas]:::page;
        D -- Isi Form & Submit --> E{"<i class='fa fa-cogs'></i> Controller:<br>TaskController@store"}:::process;
        E -- Validasi Gagal --> D;
        E -- Validasi Sukses --> F["<i class='fa fa-sync-alt'></i> Simpan & Refresh Daftar"]:::process;
        F --> A;

        A --> G["<i class='fa fa-edit'></i> Aksi: Edit Tugas"]:::action;
        G --> H["<i class='fa fa-arrow-right'></i> Buka Halaman<br>Edit Tugas (tasks.edit)"]:::page;

        A --> I["<i class='fa fa-trash-alt'></i> Aksi: Hapus Tugas"]:::action;
        I -- Konfirmasi --> J{"<i class='fa fa-cogs'></i> Controller:<br>TaskController@destroy"}:::process;
        J --> F;

        A --> K["<i class='fa fa-search'></i> Lihat Detail Tugas (Expand)"];
        K --> L["Tampilkan Rincian:<br>- Deskripsi<br>- Sub-Tugas<br>- Komentar<br>- Lampiran"];
    end

    classDef page fill:#EBF5FB,stroke:#3498DB,color:#2874A6;
    classDef action fill:#FEF9E7,stroke:#F1C40F,color:#B7950B;
    classDef process fill:#E8F8F5,stroke:#1ABC9C,color:#148F77;
                        </pre>
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
