<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            <i class="fas fa-file-signature mr-2"></i>
            {{ __('Alur Kerja Modul SK Penugasan') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">

            <!-- Intro Card -->
            <x-card>
                <div class="p-6">
                    <h3 class="text-xl font-bold text-gray-800 mb-2">Dokumentasi Alur Kerja SK Penugasan</h3>
                    <p class="text-gray-600">Halaman ini berisi dokumentasi lengkap mengenai alur kerja Modul SK Penugasan, dari pembuatan, pengelolaan, hingga penghapusan.</p>
                </div>
            </x-card>

            <!-- Flowchart Umum -->
            <x-card>
                <div class="p-6">
                    <h3 class="text-xl font-bold text-gray-800 mb-4 border-b pb-2">Flowchart Alur Kerja</h3>
                    <p class="text-gray-600 mb-6">Flowchart ini merinci keseluruhan alur kerja standar (CRUD - Create, Read, Update, Delete) untuk modul SK Penugasan.</p>
                    <div class="p-4 bg-gray-50 rounded-lg text-center">
                        <pre class="mermaid">
graph TD
    classDef page fill:#EBF5FB,stroke:#3498DB,color:#2874A6,stroke-width:1px;
    classDef action fill:#FEF9E7,stroke:#F1C40F,color:#B7950B,stroke-width:1px;
    classDef process fill:#E8F8F5,stroke:#1ABC9C,color:#148F77,stroke-width:1px;
    classDef decision fill:#FDEDEC,stroke:#C0392B,color:#A93226,stroke-width:1px;

    subgraph "A. Alur Utama"
        A1["<i class='fa fa-list-alt'></i> Halaman Daftar SK"]:::page -->|Klik 'Tambah SK Baru'| B_Flow["<i class='fa fa-plus-circle'></i> Alur Pembuatan SK"];
        A1 -->|Klik 'Edit' pada SK| C_Flow["<i class='fa fa-edit'></i> Alur Edit SK"];
        A1 -->|Klik 'Hapus' pada SK| D_Flow["<i class='fa fa-trash-alt'></i> Alur Hapus SK"];
        A1 -->|Gunakan Filter/Pencarian| A1;
    end

    subgraph B_Flow [B. Alur Pembuatan SK Penugasan]
        B1[Mulai] --> B2{<i class='fa fa-shield-alt'></i> Cek Izin: 'create'}:::decision;
        B2 -- Diizinkan --> B3["<i class='fa fa-keyboard'></i> Form Tambah SK"]:::page;
        B3 -- Isi Form & Submit --> B4{<i class='fa fa-check-double'></i> Validasi Input}:::decision;
        B4 -- Gagal --> B3;
        B4 -- Sukses --> B5["<i class='fa fa-save'></i> Simpan SK & Anggota"]:::process;
        B5 --> B6{Ingin Buat Dokumen SK?}:::decision;
        B6 -- Ya --> B7["<i class='fa fa-file-word'></i> Generate Dokumen Surat (SK)"]:::process;
        B7 --> A1;
        B6 -- Tidak --> A1;
    end

    subgraph C_Flow [C. Alur Edit SK Penugasan]
        C1[Klik 'Edit'] --> C2{<i class='fa fa-shield-alt'></i> Cek Izin: 'update'}:::decision;
        C2 -- Diizinkan --> C3["<i class='fa fa-file-alt'></i> Form Edit SK"]:::page;
        C3 -- Ubah Data & Submit --> C4{<i class='fa fa-check-double'></i> Validasi Input}:::decision;
        C4 -- Gagal --> C3;
        C4 -- Sukses --> C5["<i class='fa fa-sync-alt'></i> Update SK & Anggota"]:::process;
        C5 --> A1;
    end

    subgraph D_Flow [D. Alur Hapus SK Penugasan]
        D1[Klik 'Hapus'] --> D2{<i class='fa fa-shield-alt'></i> Cek Izin: 'delete'}:::decision;
        D2 -- Diizinkan --> D3{Yakin ingin menghapus?}:::decision;
        D3 -- Ya --> D4["<i class='fa fa-trash'></i> Hapus SK dari Database"]:::process;
        D3 -- Tidak --> A1;
        D4 --> A1;
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
                            <h4 class="font-semibold text-gray-800">1. Halaman Daftar SK Penugasan (A)</h4>
                            <p>Ini adalah halaman utama untuk modul ini. Pengguna dapat melihat daftar semua SK Penugasan yang relevan dengan mereka (dibuat oleh mereka, atau di mana mereka menjadi anggota). Halaman ini dilengkapi dengan fitur pencarian dan filter berdasarkan status dan personel.</p>
                        </div>
                        <div>
                            <h4 class="font-semibold text-gray-800">2. Alur Pembuatan SK (B)</h4>
                            <p>Pengguna dengan izin dapat membuat SK Penugasan baru. Alur ini memiliki fitur integrasi dengan modul persuratan:</p>
                            <ul class="list-disc list-inside ml-4 space-y-2">
                                <li><strong>Formulir</strong>: Pengguna mengisi detail penugasan seperti judul, tanggal, deskripsi, dan anggota tim beserta perannya.</li>
                                <li><strong>Opsi Pembuatan Dokumen SK</strong>: Pengguna memiliki opsi untuk secara otomatis men-generate dokumen surat (SK) resmi berdasarkan template yang ada. Jika opsi ini dipilih, sistem akan membuat entri baru di modul `Surat` dan menautkannya dengan SK Penugasan ini.</li>
                                <li><strong>Penyimpanan</strong>: Setelah validasi, data penugasan dan anggota tim disimpan. Jika pembuatan dokumen dipilih, dokumen surat juga akan dibuat.</li>
                            </ul>
                        </div>
                        <div>
                            <h4 class="font-semibold text-gray-800">3. Alur Edit & Hapus (C & D)</h4>
                            <p>Pengguna dengan izin yang sesuai dapat mengubah atau menghapus SK Penugasan yang sudah ada. Proses ini merupakan alur kerja standar:</p>
                             <ul class="list-disc list-inside ml-4 space-y-2">
                                <li><strong>Edit</strong>: Membuka form yang sudah terisi dengan data SK yang ada untuk diubah.</li>
                                <li><strong>Hapus</strong>: Akan meminta konfirmasi sebelum menghapus data secara permanen dari sistem.</li>
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
