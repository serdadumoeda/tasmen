<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            <i class="fas fa-users-cog mr-2"></i>
            {{ __('Alur Kerja Manajemen Pengguna') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">

            <x-card>
                <div class="p-6">
                    <h3 class="text-xl font-bold text-gray-800 mb-2">Dokumentasi Alur Kerja Pengguna</h3>
                    <p class="text-gray-600">Halaman ini berisi dokumentasi lengkap mengenai alur kerja Modul Manajemen Pengguna, mulai dari pembuatan, pembaruan, hingga pengarsipan dan penghapusan pengguna.</p>
                </div>
            </x-card>

            <x-card>
                <div class="p-6">
                    <h3 class="text-xl font-bold text-gray-800 mb-4 border-b pb-2">Flowchart Alur Kerja</h3>
                    <p class="text-gray-600 mb-6">Flowchart ini merinci keseluruhan proses utama dalam pengelolaan data pengguna.</p>
                    <div class="p-4 bg-gray-50 rounded-lg text-center">
                        <pre class="mermaid">
graph TD
    classDef start fill:#28a745,stroke:#333,stroke-width:2px,color:#fff;
    classDef end fill:#dc3545,stroke:#333,stroke-width:2px,color:#fff;
    classDef page fill:#EBF5FB,stroke:#3498DB,color:#2874A6,stroke-width:1px;
    classDef action fill:#FEF9E7,stroke:#F1C40F,color:#B7950B,stroke-width:1px;
    classDef process fill:#E8F8F5,stroke:#1ABC9C,color:#148F77,stroke-width:1px;
    classDef decision fill:#FDEDEC,stroke:#C0392B,color:#A93226,stroke-width:1px;

    subgraph sg1 [Manajemen Aktif]
        A[Mulai]:::start --> B["<i class='fa fa-users'></i> Menu Manajemen Tim"]:::page;
        B --> C["<i class='fa fa-list'></i> Daftar Pengguna Aktif"]:::page;
        C --> D["<i class='fa fa-plus-circle'></i> Tambah Pengguna"]:::action;
        C --> E["<i class='fa fa-edit'></i> Edit Pengguna"]:::action;
        C --> F["<i class='fa fa-archive'></i> Arsipkan Pengguna"]:::action;
        C --> G["<i class='fa fa-file-csv'></i> Impor Pengguna"]:::action;
        C --> H{Lihat Arsip?}:::decision;
    end

    subgraph sg2 [Proses Tambah/Edit]
        D --> I["<i class='fa fa-keyboard'></i> Isi Form Data"]:::page;
        E --> I;
        I --> J{Validasi Data?}:::decision;
        J -- Valid --> K["<i class='fa fa-save'></i> Simpan/Update Data"]:::process;
        J -- Tidak Valid --> L["<i class='fa fa-exclamation-triangle'></i> Tampilkan Error"]:::process;
        K --> C;
        L --> I;
    end

    subgraph sg3 [Proses Arsip]
        F --> M{Konfirmasi Arsip?}:::decision;
        M -- Ya --> N["<i class='fa fa-cogs'></i> Ubah Status 'Suspended'<br>Kosongkan Jabatan<br>Alihkan Bawahan"]:::process;
        M -- Tidak --> C;
        N --> O["<i class='fa fa-archive'></i> Pengguna Masuk Arsip"]:::process;
    end

    subgraph sg4 [Manajemen Arsip]
        H -- Ya --> P["<i class='fa fa-list-alt'></i> Daftar Pengguna Arsip"]:::page;
        P --> Q["<i class='fa fa-user-check'></i> Aktifkan Kembali"]:::action;
        P --> R["<i class='fa fa-trash-alt'></i> Hapus Permanen"]:::action;
        R --> S{Konfirmasi Hapus?}:::decision;
        S -- Ya --> T["<i class='fa fa-database'></i> Hapus dari DB"]:::process;
        S -- Tidak --> P;
        T --> P;
        Q --> U{Konfirmasi Aktivasi?}:::decision;
        U -- Ya --> V["<i class='fa fa-cogs'></i> Ubah Status 'Active'"]:::process;
        U -- Tidak --> P;
        V --> C;
        H -- Tidak --> Z[Selesai]:::end;
    end

    O --> P;
                        </pre>
                    </div>
                </div>
            </x-card>

            <x-card>
                <div class="p-6">
                    <h3 class="text-xl font-bold text-gray-800 mb-4 border-b pb-2">Deskripsi Alur Kerja</h3>
                    <div class="prose max-w-none text-gray-700 space-y-4">
                        <div>
                            <h4 class="font-semibold text-gray-800">1. Manajemen Pengguna Aktif</h4>
                            <p>Admin mengakses menu <strong>Manajemen Tim</strong> untuk melihat dan mengelola semua pengguna yang aktif. Dari halaman utama, Admin dapat melakukan aksi-aksi berikut:</p>
                            <ul class="list-disc list-inside ml-4 space-y-2">
                                <li><strong>Tambah Pengguna</strong>: Membuka form untuk membuat pengguna baru, lengkap dengan data diri, unit kerja, jabatan, dan atasan.</li>
                                <li><strong>Edit Pengguna</strong>: Mengubah data pengguna yang sudah ada.</li>
                                <li><strong>Impor Pengguna</strong>: Melakukan impor data pengguna secara massal melalui upload file CSV.</li>
                                <li><strong>Arsipkan Pengguna</strong>: Memulai proses untuk menonaktifkan pengguna.</li>
                            </ul>
                        </div>
                        <div>
                            <h4 class="font-semibold text-gray-800">2. Proses Pengarsipan</h4>
                            <p>Pengguna yang tidak lagi aktif (karena pensiun, mutasi, dll.) tidak langsung dihapus, melainkan diarsipkan untuk menjaga integritas data historis.</p>
                             <ul class="list-disc list-inside ml-4 space-y-2">
                                <li><strong>Konfirmasi</strong>: Sistem akan meminta konfirmasi sebelum mengarsipkan.</li>
                                <li><strong>Proses Sistem</strong>: Status pengguna diubah menjadi `suspended`, jabatannya dikosongkan, dan jika ia memiliki bawahan, maka bawahan tersebut akan secara otomatis dialihkan ke atasan dari pengguna yang diarsipkan.</li>
                                <li><strong>Masuk ke Arsip</strong>: Pengguna yang telah diproses akan hilang dari daftar aktif dan muncul di daftar arsip.</li>
                            </ul>
                        </div>
                        <div>
                            <h4 class="font-semibold text-gray-800">3. Manajemen Arsip</h4>
                            <p>Di halaman arsip, Admin dapat mengelola pengguna yang telah dinonaktifkan:</p>
                             <ul class="list-disc list-inside ml-4 space-y-2">
                                <li><strong>Aktifkan Kembali</strong>: Mengubah status pengguna kembali menjadi `active`. Setelah diaktifkan, Admin perlu mengatur ulang jabatan dan unit kerja pengguna secara manual.</li>
                                <li><strong>Hapus Permanen</strong>: Menghapus data pengguna secara permanen dari sistem. Aksi ini tidak dapat dibatalkan.</li>
                            </ul>
                        </div>
                         <div>
                            <h4 class="font-semibold text-gray-800">4. Peniruan (Impersonate)</h4>
                            <p>Superadmin memiliki kemampuan untuk "meniru" akun pengguna lain. Fitur ini sangat berguna untuk debugging atau memberikan bantuan teknis seolah-olah sebagai pengguna tersebut, tanpa memerlukan password mereka.</p>
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
