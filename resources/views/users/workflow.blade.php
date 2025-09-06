<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Alur Kerja Manajemen Pengguna') }}
            </h2>
            <a href="{{ route('users.index') }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-lg font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150 transform hover:scale-105">
                <i class="fas fa-arrow-left mr-2"></i> {{ __('Kembali ke Daftar Pengguna') }}
            </a>
        </div>
    </x-slot>

    <div class="py-12 bg-gray-50">
        <div class="max-w-screen-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl rounded-xl border border-gray-200">
                <div class="p-6 sm:px-10 bg-white border-b border-gray-200">
                    <h3 class="text-2xl font-bold text-gray-800">Diagram Alur Kerja</h3>
                    <p class="text-gray-500 mt-2">Diagram ini menjelaskan proses utama dalam pengelolaan data pengguna, mulai dari pembuatan hingga penghapusan.</p>
                </div>
                <div class="p-6 sm:px-10 text-center">
                    <div class="mermaid">
                        graph TD
                            A[Mulai] --> B{Akses Menu<br>Manajemen Tim};
                            B --> C[Tampil Daftar Pengguna Aktif];

                            subgraph "Aksi Utama"
                                C --> D[Tambah Pengguna Baru];
                                C --> E[Edit Pengguna];
                                C --> F[Non-Aktifkan Pengguna];
                                C --> G[Impor Pengguna Massal];
                            end

                            D --> H[Isi Form Data Pengguna];
                            H --> I{Validasi Data};
                            I -- Valid --> J[Simpan Pengguna Baru &<br>Tentukan Role];
                            I -- Tidak Valid --> K[Tampilkan Pesan Error];
                            J --> C;
                            K --> H;

                            E --> L[Pilih Pengguna];
                            L --> M[Ubah Data pada Form];
                            M --> N{Validasi Data};
                            N -- Valid --> O[Update Data Pengguna];
                            N -- Tidak Valid --> P[Tampilkan Pesan Error];
                            O --> C;
                            P --> M;

                            F --> Q[Pilih Pengguna];
                            Q --> R{Konfirmasi};
                            R -- Ya --> S[Ubah Status jadi 'Suspended',<br>Kosongkan Jabatan,<br>Alihkan Bawahan];
                            S --> T[Pengguna Masuk ke Daftar Arsip];
                            R -- Tidak --> C;

                            G --> U[Upload File CSV];
                            U --> V{Validasi Format File};
                            V -- Valid --> W[Proses Impor Data];
                            V -- Tidak Valid --> X[Tampilkan Pesan Error];
                            W --> C;
                            X --> G;

                            C --> Y{Lihat Daftar Arsip?};
                            Y -- Ya --> Z[Tampil Daftar Pengguna<br>yang Dinon-Aktifkan];
                            Y -- Tidak --> End[Selesai];

                            subgraph "Aksi Arsip"
                                Z --> AA[Aktifkan Kembali];
                                Z --> AB[Hapus Permanen];
                            end

                            AA --> AC[Pilih Pengguna];
                            AC --> AD{Konfirmasi};
                            AD -- Ya --> AE[Ubah Status jadi 'Active'];
                            AD -- Tidak --> Z;
                            AE --> C;

                            AB --> AF[Pilih Pengguna];
                            AF --> AG{Konfirmasi};
                            AG -- Ya --> AH[Hapus Data Pengguna<br>dari Database];
                            AG -- Tidak --> Z;
                            AH --> Z;

                            T --> Z;

                            style A fill:#28a745,stroke:#333,stroke-width:2px,color:#fff
                            style End fill:#dc3545,stroke:#333,stroke-width:2px,color:#fff
                    </div>
                </div>
            </div>

            <div class="mt-8 bg-white overflow-hidden shadow-xl rounded-xl border border-gray-200">
                <div class="p-6 sm:px-10 bg-white border-b border-gray-200">
                    <h3 class="text-2xl font-bold text-gray-800">Deskripsi Alur Kerja</h3>
                    <p class="text-gray-500 mt-2">Penjelasan detail untuk setiap langkah dalam proses manajemen pengguna.</p>
                </div>
                <div class="p-6 sm:px-10">
                    <dl class="space-y-6">
                        <div>
                            <dt class="font-semibold text-lg text-gray-800">1. Akses Menu & Daftar Pengguna</dt>
                            <dd class="mt-1 text-gray-600">Admin mengakses menu "Manajemen Tim" untuk melihat daftar semua pengguna aktif dalam sistem. Dari halaman ini, Admin dapat melakukan pencarian, melihat detail, dan memulai berbagai aksi.</dd>
                        </div>
                        <div>
                            <dt class="font-semibold text-lg text-gray-800">2. Proses Tambah Pengguna</dt>
                            <dd class="mt-1 text-gray-600">Admin memilih "Tambah Pengguna" dan mengisi form dengan data lengkap seperti nama, email, NIP, jabatan, unit kerja, dan atasan. Setelah divalidasi, pengguna baru akan dibuat dan role-nya akan disinkronkan secara otomatis berdasarkan unit kerjanya.</dd>
                        </div>
                        <div>
                            <dt class="font-semibold text-lg text-gray-800">3. Proses Edit Pengguna</dt>
                            <dd class="mt-1 text-gray-600">Admin dapat mengubah data pengguna yang sudah ada. Perubahan data akan divalidasi sebelum disimpan. Jika terjadi perpindahan unit, atasan pengguna tersebut akan di-reset.</dd>
                        </div>
                         <div>
                            <dt class="font-semibold text-lg text-gray-800">4. Proses Impor Pengguna</dt>
                            <dd class="mt-1 text-gray-600">Untuk efisiensi, Admin dapat mengimpor banyak pengguna sekaligus menggunakan file CSV dengan format yang telah ditentukan. Sistem akan memvalidasi file sebelum memproses data.</dd>
                        </div>
                        <div>
                            <dt class="font-semibold text-lg text-gray-800">5. Proses Non-Aktifkan (Arsipkan) Pengguna</dt>
                            <dd class="mt-1 text-gray-600">Pengguna yang sudah tidak aktif (misal: pensiun, mutasi) tidak langsung dihapus, melainkan diarsipkan. Proses ini akan mengubah status pengguna menjadi 'suspended', mengosongkan jabatan yang dipegangnya, dan mengalihkan bawahannya (jika ada) ke atasan dari pengguna yang dinon-aktifkan.</dd>
                        </div>
                        <div>
                            <dt class="font-semibold text-lg text-gray-800">6. Manajemen Arsip</dt>
                            <dd class="mt-1 text-gray-600">Admin dapat mengakses halaman "Lihat Arsip" untuk melihat daftar pengguna yang dinon-aktifkan. Dari sini, Admin memiliki dua opsi:</dd>
                            <dd class="mt-2 text-gray-600 pl-4">
                                <ul class="list-disc list-inside space-y-1">
                                    <li><b>Aktifkan Kembali:</b> Mengembalikan status pengguna menjadi 'active'. Pengguna tersebut harus di-assign kembali ke sebuah jabatan secara manual.</li>
                                    <li><b>Hapus Permanen:</b> Menghapus data pengguna secara permanen dari database. Aksi ini tidak dapat dibatalkan dan hanya boleh dilakukan jika data pengguna sudah benar-benar tidak diperlukan lagi.</li>
                                </ul>
                            </dd>
                        </div>
                         <div>
                            <dt class="font-semibold text-lg text-gray-800">7. Peniruan (Impersonate)</dt>
                            <dd class="mt-1 text-gray-600">Superadmin memiliki kemampuan untuk "meniru" akun pengguna lain. Fitur ini sangat berguna untuk debugging atau memberikan bantuan teknis seolah-olah sebagai pengguna tersebut, tanpa memerlukan password mereka.</dd>
                        </div>
                    </dl>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

@push('scripts')
<script src="{{ asset('assets/plugins/mermaid/mermaid.min.js') }}"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        mermaid.initialize({ startOnLoad: true });
    });
</script>
@endpush
