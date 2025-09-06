<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Alur Kerja Manajemen Unit') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-2xl font-bold text-gray-800">Diagram Alur Kerja</h3>
                        <a href="{{ route('admin.units.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-900 focus:outline-none focus:border-gray-900 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150">
                            <i class="fas fa-arrow-left mr-2"></i>
                            Kembali ke Daftar Unit
                        </a>
                    </div>
                    <div class="p-4 bg-gray-50 rounded-lg text-center">
                        <div class="mermaid">
                        graph TD
                            A[Mulai] --> B{Akses Menu<br>Manajemen Unit};
                            B --> C[Sistem Menampilkan<br>Daftar Unit Kerja];
                            C --> D{Pilih Aksi};
                            D --> E[Tambah Unit Baru];
                            D --> F[Edit Unit];
                            D --> G[Hapus Unit];

                            E --> H[Isi Form Data Unit<br>(Nama, Induk Unit, dll)];
                            H --> I{Validasi Data};
                            I -- Valid --> J[Simpan Unit Baru ke Database];
                            I -- Tidak Valid --> K[Tampilkan Pesan Error];
                            J --> C;
                            K --> H;

                            F --> L[Pilih Unit yang Akan Diubah];
                            L --> M[Ubah Data pada Form];
                            M --> N{Validasi Data};
                            N -- Valid --> O[Update Data Unit di Database];
                            N -- Tidak Valid --> P[Tampilkan Pesan Error];
                            O --> C;
                            P --> M;

                            G --> Q[Pilih Unit yang Akan Dihapus];
                            Q --> R{Konfirmasi Hapus};
                            R -- Ya --> S{Pengecekan Ketergantungan<br>(e.g. Pegawai di Unit tsb)};
                            R -- Tidak --> C;
                            S -- Ada Ketergantungan --> T[Tampilkan Pesan Error<br>Hapus Gagal];
                            S -- Tidak Ada Ketergantungan --> U[Hapus Data Unit dari Database];
                            T --> C;
                            U --> C;
                            C --> V[Selesai];


                            style A fill:#28a745,stroke:#333,stroke-width:2px,color:#fff
                            style V fill:#dc3545,stroke:#333,stroke-width:2px,color:#fff
                            style C fill:#17a2b8,stroke:#333,stroke-width:2px,color:#fff
                            style J fill:#4CAF50,stroke:#333,stroke-width:2px,color:#fff
                            style O fill:#4CAF50,stroke:#333,stroke-width:2px,color:#fff
                            style U fill:#4CAF50,stroke:#333,stroke-width:2px,color:#fff
                        </div>
                    </div>
                </div>

                <div class="p-6 bg-white border-b border-gray-200">
                     <h3 class="text-2xl font-bold text-gray-800 mb-4">Deskripsi Alur Kerja</h3>
                    <dl class="row">
                        <dt class="col-sm-3">1. Akses Menu</dt>
                        <dd class="col-sm-9">Superadmin atau Admin mengakses menu "Manajemen Unit" dari sidebar navigasi untuk memulai proses pengelolaan unit kerja.</dd>

                        <dt class="col-sm-3">2. Tampilan Daftar Unit</dt>
                        <dd class="col-sm-9">Sistem akan menampilkan halaman yang berisi daftar semua unit kerja yang sudah terdaftar, biasanya dalam format tabel yang informatif.</dd>

                        <dt class="col-sm-3">3. Pilihan Aksi</dt>
                        <dd class="col-sm-9">Pada halaman daftar unit, tersedia beberapa tombol aksi:</dd>
                        <dd class="col-sm-9 offset-sm-3">
                            <ul>
                                <li><b>Tambah Unit Baru:</b> Untuk membuat unit kerja baru.</li>
                                <li><b>Edit:</b> Untuk mengubah data unit kerja yang sudah ada.</li>
                                <li><b>Hapus:</b> Untuk menghapus unit kerja dari sistem.</li>
                            </ul>
                        </dd>

                        <dt class="col-sm-3">4. Proses Tambah Unit</dt>
                        <dd class="col-sm-9">Saat memilih "Tambah Unit", pengguna akan diarahkan ke form pembuatan unit. Setelah mengisi data dan menyimpan, sistem akan memvalidasi input. Jika valid, unit baru akan tersimpan dan daftar unit akan diperbarui. Jika tidak, pesan kesalahan akan ditampilkan.</dd>

                        <dt class="col-sm-3">5. Proses Edit Unit</dt>
                        <dd class="col-sm-9">Pengguna memilih unit yang ingin diubah dan mengklik tombol "Edit". Form akan terisi dengan data unit saat ini. Setelah mengubah data, sistem akan melakukan validasi sebelum menyimpan perubahan.</dd>

                        <dt class="col-sm-3">6. Proses Hapus Unit</dt>
                        <dd class="col-sm-9">Setelah memilih "Hapus" pada unit tertentu, sistem akan meminta konfirmasi. Sebelum benar-benar menghapus, sistem akan memeriksa apakah ada data lain yang bergantung pada unit tersebut (misalnya, pegawai yang terdaftar di unit itu). Jika ada, penghapusan akan dibatalkan untuk menjaga integritas data. Jika tidak ada, unit akan dihapus.</dd>

                        <dt class="col-sm-3">7. Selesai</dt>
                        <dd class="col-sm-9">Semua proses (tambah, edit, hapus) akan berakhir dengan sistem menampilkan kembali daftar unit kerja yang telah diperbarui.</dd>
                    </dl>
                </div>
            </div>
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
