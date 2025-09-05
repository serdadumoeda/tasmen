<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            <i class="fas fa-sitemap mr-2"></i>
            {{ __('Alur Kerja Modul Klasifikasi Surat') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">

            <!-- Intro Card -->
            <x-card>
                <div class="p-6">
                    <h3 class="text-xl font-bold text-gray-800 mb-2">Dokumentasi Alur Kerja Klasifikasi Surat</h3>
                    <p class="text-gray-600">Halaman ini berisi dokumentasi lengkap mengenai alur kerja Modul Manajemen Klasifikasi Surat, yang merupakan sistem untuk mengelola kode klasifikasi arsip surat.</p>
                </div>
            </x-card>

            <!-- Flowchart Umum -->
            <x-card>
                <div class="p-6">
                    <h3 class="text-xl font-bold text-gray-800 mb-4 border-b pb-2">Flowchart Alur Kerja</h3>
                    <p class="text-gray-600 mb-6">Flowchart ini merinci keseluruhan alur kerja standar (CRUD - Create, Read, Update, Delete) untuk modul Klasifikasi Surat.</p>
                    <div class="p-4 bg-gray-50 rounded-lg text-center">
                        <pre class="mermaid">
graph TD
    classDef page fill:#EBF5FB,stroke:#3498DB,color:#2874A6,stroke-width:1px;
    classDef action fill:#FEF9E7,stroke:#F1C40F,color:#B7950B,stroke-width:1px;
    classDef process fill:#E8F8F5,stroke:#1ABC9C,color:#148F77,stroke-width:1px;
    classDef decision fill:#FDEDEC,stroke:#C0392B,color:#A93226,stroke-width:1px;

    subgraph "A. Alur Utama"
        A1["Halaman Daftar Klasifikasi"]:::page -->|Klik 'Tambah Klasifikasi'| B_Flow["Alur Pembuatan"];
        A1 -->|Klik 'Edit'| C_Flow["Alur Edit"];
        A1 -->|Klik 'Hapus'| D_Flow["Alur Hapus"];
    end

    subgraph B_Flow [B. Alur Pembuatan Klasifikasi]
        B1[Mulai] --> B2["Form Tambah Klasifikasi"]:::page;
        B2 -- Isi Kode, Deskripsi & Induk --> B3{Validasi}:::decision;
        B3 -- Gagal --> B2;
        B3 -- Sukses --> B4["Simpan Klasifikasi Baru"]:::process;
        B4 --> A1;
    end

    subgraph C_Flow [C. Alur Edit Klasifikasi]
        C1[Klik 'Edit'] --> C2["Form Edit Klasifikasi"]:::page;
        C2 -- Ubah Data & Submit --> C3{Validasi}:::decision;
        C3 -- Gagal --> C2;
        C3 -- Sukses --> C4["Update Klasifikasi"]:::process;
        C4 --> A1;
    end

    subgraph D_Flow [D. Alur Hapus Klasifikasi]
        D1[Klik 'Hapus'] --> D2{Yakin ingin menghapus?}:::decision;
        D2 -- Ya --> D3["Hapus Klasifikasi"]:::process;
        D2 -- Tidak --> A1;
        D3 --> A1;
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
                            <h4 class="font-semibold text-gray-800">1. Halaman Daftar Klasifikasi (A)</h4>
                            <p>Ini adalah halaman utama untuk modul ini, diakses oleh Admin. Pengguna dapat melihat daftar semua kode klasifikasi surat yang tersedia dalam format tabel. Dari halaman ini, semua aksi pengelolaan klasifikasi dimulai.</p>
                        </div>
                        <div>
                            <h4 class="font-semibold text-gray-800">2. Alur Pembuatan Klasifikasi (B)</h4>
                            <p>Admin dapat membuat kode klasifikasi baru.</p>
                            <ul class="list-disc list-inside ml-4 space-y-2">
                                <li><strong>Formulir</strong>: Admin mengisi Kode Klasifikasi (misal: KP.02.01), Deskripsi, dan secara opsional memilih Induk Klasifikasi untuk membuat struktur hierarkis.</li>
                                <li><strong>Penyimpanan</strong>: Setelah divalidasi, data disimpan ke database dan akan muncul di daftar.</li>
                            </ul>
                        </div>
                        <div>
                            <h4 class="font-semibold text-gray-800">3. Alur Edit & Hapus (C & D)</h4>
                            <p>Admin dapat mengubah atau menghapus klasifikasi yang sudah ada. Proses ini merupakan alur kerja CRUD yang standar dan langsung.</p>
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
