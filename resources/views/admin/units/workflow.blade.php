<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            <i class="fas fa-sitemap mr-2"></i>
            {{ __('Alur Kerja Manajemen Unit Kerja') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">

            <x-card>
                <div class="p-6">
                    <h3 class="text-xl font-bold text-gray-800 mb-2">Dokumentasi Alur Kerja Unit Kerja</h3>
                    <p class="text-gray-600">Halaman ini menjelaskan proses pengelolaan unit kerja, dari pembuatan unit baru, pengeditan, hingga penghapusan, serta bagaimana struktur organisasi dikelola.</p>
                </div>
            </x-card>

            <x-card>
                <div class="p-6">
                    <h3 class="text-xl font-bold text-gray-800 mb-4 border-b pb-2">Flowchart Alur Kerja</h3>
                    <p class="text-gray-600 mb-6">Flowchart ini merinci keseluruhan proses utama dalam pengelolaan data unit kerja.</p>
                    <div class="p-4 bg-gray-50 rounded-lg text-center">
                        <pre class="mermaid">
graph TD
    classDef page fill:#EBF5FB,stroke:#3498DB,color:#2874A6;
    classDef action fill:#FEF9E7,stroke:#F1C40F,color:#B7950B;
    classDef process fill:#E8F8F5,stroke:#1ABC9C,color:#148F77;
    classDef decision fill:#FDEDEC,stroke:#C0392B,color:#A93226;

    subgraph sg1 [Aksi Utama]
        A[Mulai] --> B(Menu Manajemen Unit):::page;
        B --> C(Daftar Unit Kerja):::page;
        C --> D{Pilih Aksi}:::decision;
        D -- Tambah --> E(Tambah Unit Baru):::action;
        D -- Edit --> F(Edit Unit):::action;
        D -- Hapus --> G(Hapus Unit):::action;
    end

    subgraph sg2 [Proses Tambah/Edit]
        E --> H(Isi Form Data Unit):::page;
        F --> H;
        H --> I{Data Valid?}:::decision;
        I -- Ya --> J(Simpan/Update Unit):::process;
        I -- Tidak --> K(Tampilkan Error):::process;
        J --> C;
        K --> H;
    end

    subgraph sg3 [Proses Hapus]
        G --> L{Konfirmasi Hapus?}:::decision;
        L -- Ya --> M{Cek Ketergantungan?}:::decision;
        L -- Tidak --> C;
        M -- Ada --> N(Hapus Gagal):::process;
        M -- Tidak Ada --> O(Hapus Unit dari DB):::process;
        N --> C;
        O --> C;
    end

    C --> Z[Selesai];

                        </pre>
                    </div>
                </div>
            </x-card>

            <x-card>
                <div class="p-6">
                    <h3 class="text-xl font-bold text-gray-800 mb-4 border-b pb-2">Deskripsi Alur Kerja</h3>
                    <div class="prose max-w-none text-gray-700 space-y-4">
                        <div>
                            <h4 class="font-semibold text-gray-800">1. Akses & Tampilan Utama</h4>
                            <p>Admin mengakses menu <strong>Manajemen Unit</strong> untuk melihat daftar semua unit kerja yang ada dalam sistem.</p>
                        </div>
                        <div>
                            <h4 class="font-semibold text-gray-800">2. Tambah & Edit Unit Kerja</h4>
                            <p>Admin dapat membuat unit kerja baru atau mengubah yang sudah ada dengan mengisi formulir yang berisi informasi seperti Nama Unit, Induk Unit, dan Kepala Unit.</p>
                        </div>
                         <div>
                            <h4 class="font-semibold text-gray-800">3. Hapus Unit Kerja</h4>
                            <p>Sebelum menghapus sebuah unit, sistem melakukan validasi penting untuk menjaga integritas data. Sistem akan memeriksa apakah masih ada sub-unit, jabatan, atau pegawai yang terikat pada unit tersebut. Jika ada, penghapusan akan dibatalkan.</p>
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
