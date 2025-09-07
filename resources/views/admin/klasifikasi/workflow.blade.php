<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            <i class="fas fa-book-dead mr-2"></i>
            {{ __('Alur Kerja Manajemen Klasifikasi Surat') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">

            <x-card>
                <div class="p-6">
                    <h3 class="text-xl font-bold text-gray-800 mb-2">Dokumentasi Alur Klasifikasi Surat</h3>
                    <p class="text-gray-600">Halaman ini menjelaskan proses pengelolaan data master untuk klasifikasi surat, yang menjadi dasar penomoran surat otomatis.</p>
                </div>
            </x-card>

            <x-card>
                <div class="p-6">
                    <h3 class="text-xl font-bold text-gray-800 mb-4 border-b pb-2">Flowchart Alur Kerja</h3>
                    <p class="text-gray-600 mb-6">Flowchart ini merinci proses standar CRUD untuk pengelolaan klasifikasi dan dampaknya pada modul lain.</p>
                    <div class="p-4 bg-gray-50 rounded-lg text-center">
                        <pre class="mermaid">
graph TD
    classDef page fill:#EBF5FB,stroke:#3498DB,color:#2874A6,stroke-width:1px;
    classDef action fill:#FEF9E7,stroke:#F1C40F,color:#B7950B,stroke-width:1px;
    classDef process fill:#E8F8F5,stroke:#1ABC9C,color:#148F77,stroke-width:1px;
    classDef decision fill:#FDEDEC,stroke:#C0392B,color:#A93226,stroke-width:1px;
    classDef effect fill:#F5EEF8,stroke:#9B59B6,color:#7D3C98,stroke-width:1px;

    subgraph "A. Pengelolaan Master Data"
        A1["<i class='fa fa-user-shield'></i> Admin"]:::action --> A2["<i class='fa fa-list-alt'></i> Halaman Daftar Klasifikasi"]:::page;
        A2 --> A3{Pilih Aksi}:::decision;
        A3 -- Tambah --> A4["<i class='fa fa-plus-circle'></i> Form Tambah<br>(Kode, Deskripsi, Induk)"]:::page;
        A3 -- Edit --> A5["<i class='fa fa-edit'></i> Form Edit"]:::page;
        A3 -- Hapus --> A6{Konfirmasi Hapus}:::decision;
    end

    subgraph "B. Proses Simpan"
        A4 -- Isi Form --> B1{Validasi Data}:::decision;
        A5 -- Ubah Data --> B1;
        B1 -- Valid --> B2["<i class='fa fa-save'></i> Simpan/Update ke DB"]:::process;
        B1 -- Tidak Valid --> B3["<i class='fa fa-exclamation-triangle'></i> Tampilkan Error"]:::process;
        B2 --> A2;
        B3 --> A4;
    end

    subgraph "C. Proses Hapus"
        A6 -- Ya --> C1["<i class='fa fa-trash'></i> Hapus dari DB"]:::process;
        A6 -- Tidak --> A2;
        C1 --> A2;
    end

    subgraph "D. Dampak pada Sistem"
        D1["<i class='fa fa-file-signature'></i> Form Pembuatan Surat Keluar"]:::page;
        D2["<i class='fa fa-cogs'></i> NomorSuratService"]:::process;
        B2 --> D1;
        D1 -- Pilih Klasifikasi --> D2;
        D2 -- Generate --> D3["<i class='fa fa-hashtag'></i> Nomor Surat Otomatis<br>Contoh: B-123/KU.01.01/2024"]:::effect;
    end

                        </pre>
                    </div>
                </div>
            </x-card>

            <x-card>
                <div class="p-6">
                    <h3 class="text-xl font-bold text-gray-800 mb-4 border-b pb-2">Deskripsi Alur Kerja</h3>
                    <div class="prose max-w-none text-gray-700 space-y-4">
                        <div>
                            <h4 class="font-semibold text-gray-800">1. Pengelolaan Data Master Klasifikasi</h4>
                            <p>Admin mengelola daftar kode klasifikasi surat yang digunakan di seluruh organisasi. Proses ini adalah CRUD (Create, Read, Update, Delete) standar:</p>
                            <ul class="list-disc list-inside ml-4 space-y-2">
                                <li><strong>Tambah</strong>: Admin menambahkan kode klasifikasi baru, deskripsinya, dan dapat mengaturnya dalam sebuah hierarki dengan menunjuk "induk" klasifikasi.</li>
                                <li><strong>Edit</strong>: Mengubah detail kode atau deskripsi yang sudah ada.</li>
                                <li><strong>Hapus</strong>: Menghapus kode klasifikasi dari sistem.</li>
                            </ul>
                        </div>
                        <div>
                            <h4 class="font-semibold text-gray-800">2. Dampak pada Penomoran Surat</h4>
                            <p>Data master klasifikasi ini merupakan komponen kunci dalam fitur penomoran surat otomatis.</p>
                             <ul class="list-disc list-inside ml-4 space-y-2">
                                <li><strong>Pemilihan Klasifikasi</strong>: Saat pengguna membuat Surat Keluar, mereka diwajibkan untuk memilih salah satu klasifikasi dari daftar yang telah dibuat oleh Admin.</li>
                                <li><strong>Komponen Nomor Surat</strong>: Ketika surat disimpan, `NomorSuratService` akan mengambil kode dari klasifikasi yang dipilih (misalnya, `KU.01.01`) dan menggunakannya sebagai salah satu segmen dalam nomor surat final.</li>
                                <li><strong>Konsistensi</strong>: Alur ini memastikan bahwa semua surat yang keluar dari sistem memiliki format penomoran yang konsisten dan sesuai dengan standar tata naskah dinas.</li>
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
