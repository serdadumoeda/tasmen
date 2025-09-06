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
    classDef start fill:#28a745,stroke:#333,stroke-width:2px,color:#fff;
    classDef end fill:#dc3545,stroke:#333,stroke-width:2px,color:#fff;
    classDef page fill:#EBF5FB,stroke:#3498DB,color:#2874A6,stroke-width:1px;
    classDef action fill:#FEF9E7,stroke:#F1C40F,color:#B7950B,stroke-width:1px;
    classDef process fill:#E8F8F5,stroke:#1ABC9C,color:#148F77,stroke-width:1px;
    classDef decision fill:#FDEDEC,stroke:#C0392B,color:#A93226,stroke-width:1px;

    A[Mulai]:::start --> B["<i class='fa fa-building'></i> Menu Manajemen Unit"]:::page;
    B --> C["<i class='fa fa-list-ul'></i> Daftar Unit Kerja"]:::page;
    C --> D{Pilih Aksi}:::decision;

    subgraph sg1 [Aksi Utama]
        D -- Tambah --> E["<i class='fa fa-plus-circle'></i> Tambah Unit Baru"]:::action;
        D -- Edit --> F["<i class='fa fa-edit'></i> Edit Unit"]:::action;
        D -- Hapus --> G["<i class='fa fa-trash'></i> Hapus Unit"]:::action;
    end

    subgraph sg2 [Proses Tambah/Edit]
        E --> H["<i class='fa fa-keyboard'></i> Isi Form<br>(Nama, Induk Unit, Kepala Unit)"]:::page;
        F --> H;
        H --> I{Validasi Data}:::decision;
        I -- Valid --> J["<i class='fa fa-save'></i> Simpan/Update Unit"]:::process;
        I -- Tidak Valid --> K["<i class='fa fa-exclamation-triangle'></i> Tampilkan Error"]:::process;
        J --> C;
        K --> H;
    end

    subgraph sg3 [Proses Hapus]
        G --> L{Konfirmasi Hapus}:::decision;
        L -- Ya --> M{Cek Ketergantungan<br>(Pegawai/Jabatan/Sub-Unit)}:::decision;
        L -- Tidak --> C;
        M -- Ada Ketergantungan --> N["<i class='fa fa-ban'></i> Hapus Gagal<br>Tampilkan Error"]:::process;
        M -- Tidak Ada --> O["<i class='fa fa-database'></i> Hapus Unit dari DB"]:::process;
        N --> C;
        O --> C;
    end

    C --> Z[Selesai]:::end;

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
                            <p>Admin mengakses menu <strong>Manajemen Unit</strong> untuk melihat daftar semua unit kerja yang ada dalam sistem. Tampilan utama menyajikan daftar unit dalam struktur hierarkis (jika memungkinkan) atau dalam bentuk tabel.</p>
                        </div>
                        <div>
                            <h4 class="font-semibold text-gray-800">2. Tambah Unit Kerja</h4>
                            <p>Admin dapat membuat unit kerja baru dengan mengisi formulir yang berisi informasi:</p>
                             <ul class="list-disc list-inside ml-4 space-y-2">
                                <li><strong>Nama Unit</strong>: Nama resmi dari unit kerja.</li>
                                <li><strong>Induk Unit</strong>: Memilih unit kerja yang menjadi atasan dari unit baru ini, untuk membentuk struktur organisasi.</li>
                                <li><strong>Kepala Unit</strong>: Menunjuk seorang pegawai sebagai kepala unit kerja tersebut.</li>
                            </ul>
                        </div>
                        <div>
                            <h4 class="font-semibold text-gray-800">3. Edit Unit Kerja</h4>
                            <p>Informasi unit kerja yang sudah ada dapat diubah. Proses ini mirip dengan proses penambahan, di mana admin dapat memperbarui nama, induk, atau kepala unit.</p>
                        </div>
                         <div>
                            <h4 class="font-semibold text-gray-800">4. Hapus Unit Kerja</h4>
                            <p>Sebelum menghapus sebuah unit, sistem melakukan validasi penting untuk menjaga integritas data:</p>
                             <ul class="list-disc list-inside ml-4 space-y-2">
                                <li><strong>Pemeriksaan Ketergantungan</strong>: Sistem akan memeriksa apakah masih ada sub-unit, jabatan, atau pegawai yang terikat pada unit tersebut.</li>
                                <li><strong>Pencegahan Penghapusan</strong>: Jika ada ketergantungan yang ditemukan, proses penghapusan akan dibatalkan dan sistem akan memberikan pesan error yang informatif. Unit hanya bisa dihapus jika sudah tidak memiliki "tanggungan".</li>
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
