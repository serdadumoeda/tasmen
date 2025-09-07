<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            <i class="fas fa-puzzle-piece mr-2"></i>
            {{ __('Alur Kerja Modul Template Surat') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">

            <!-- Intro Card -->
            <x-card>
                <div class="p-6">
                    <h3 class="text-xl font-bold text-gray-800 mb-2">Dokumentasi Alur Kerja Template Surat</h3>
                    <p class="text-gray-600">Halaman ini berisi dokumentasi lengkap mengenai alur kerja Modul Template Surat, yang merupakan sistem dasar untuk mengelola template yang akan digunakan dalam pembuatan surat keluar.</p>
                </div>
            </x-card>

            <!-- Flowchart Umum -->
            <x-card>
                <div class="p-6">
                    <h3 class="text-xl font-bold text-gray-800 mb-4 border-b pb-2">Flowchart Alur Kerja</h3>
                    <p class="text-gray-600 mb-6">Flowchart ini merinci keseluruhan alur kerja standar (CRUD - Create, Read, Update, Delete) untuk modul Template Surat.</p>
                    <div class="p-4 bg-gray-50 rounded-lg text-center">
                        <pre class="mermaid">
graph TD
    classDef page fill:#EBF5FB,stroke:#3498DB,color:#2874A6,stroke-width:1px;
    classDef action fill:#FEF9E7,stroke:#F1C40F,color:#B7950B,stroke-width:1px;
    classDef process fill:#E8F8F5,stroke:#1ABC9C,color:#148F77,stroke-width:1px;
    classDef decision fill:#FDEDEC,stroke:#C0392B,color:#A93226,stroke-width:1px;

    subgraph "A. Alur Utama"
        A1["<i class='fa fa-list-alt'></i> Halaman Daftar Template"]:::page -->|Klik 'Tambah Template'| B_Flow["<i class='fa fa-plus-circle'></i> Alur Pembuatan"];
        A1 -->|Klik 'Edit'| C_Flow["<i class='fa fa-edit'></i> Alur Edit"];
        A1 -->|Klik 'Hapus'| D_Flow["<i class='fa fa-trash-alt'></i> Alur Hapus"];
    end

    subgraph B_Flow [B. Alur Pembuatan Template]
        B1[Mulai] --> B2["<i class='fa fa-keyboard'></i> Form Tambah Template"]:::page;
        B2 -- Isi Judul, Deskripsi & Konten --> B3{<i class='fa fa-check-double'></i> Validasi}:::decision;
        B3 -- Gagal --> B2;
        B3 -- Sukses --> B4["<i class='fa fa-save'></i> Simpan Template Baru"]:::process;
        B4 --> A1;
    end

    subgraph C_Flow [C. Alur Edit Template]
        C1[Klik 'Edit'] --> C2["<i class='fa fa-file-alt'></i> Form Edit Template"]:::page;
        C2 -- Ubah Data & Submit --> C3{<i class='fa fa-check-double'></i> Validasi}:::decision;
        C3 -- Gagal --> C2;
        C3 -- Sukses --> C4["<i class='fa fa-sync-alt'></i> Update Template"]:::process;
        C4 --> A1;
    end

    subgraph D_Flow [D. Alur Hapus Template]
        D1[Klik 'Hapus'] --> D2{Yakin ingin menghapus?}:::decision;
        D2 -- Ya --> D3["<i class='fa fa-trash'></i> Hapus Template"]:::process;
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
                            <h4 class="font-semibold text-gray-800">1. Halaman Daftar Template (A)</h4>
                            <p>Ini adalah halaman utama untuk modul ini. Pengguna dapat melihat daftar semua template surat yang tersedia. Dari halaman ini, semua aksi pengelolaan template dimulai.</p>
                        </div>
                        <div>
                            <h4 class="font-semibold text-gray-800">2. Alur Pembuatan Template (B)</h4>
                            <p>Pengguna dapat membuat template baru untuk digunakan dalam pembuatan surat keluar.</p>
                            <ul class="list-disc list-inside ml-4 space-y-2">
                                <li><strong>Formulir</strong>: Pengguna mengisi judul, deskripsi singkat, dan konten utama dari template. Konten ini mendukung HTML dan placeholder khusus (misalnya, `@{{nama_penerima}}`) yang nantinya akan diganti saat surat dibuat.</li>
                                <li><strong>Penyimpanan</strong>: Setelah divalidasi, template disimpan ke database dan akan muncul di daftar.</li>
                            </ul>
                        </div>
                        <div>
                            <h4 class="font-semibold text-gray-800">3. Alur Edit & Hapus (C & D)</h4>
                            <p>Pengguna dapat mengubah atau menghapus template yang sudah ada. Proses ini merupakan alur kerja CRUD yang standar dan langsung.</p>
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
