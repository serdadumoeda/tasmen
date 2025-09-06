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
                            %% Node Definitions
                            A[Mulai];
                            B{Akses Menu<br>Manajemen Tim};
                            C[Tampil Daftar Pengguna Aktif];
                            Y{Lihat Daftar Arsip?};
                            End[Selesai];

                            subgraph "Aksi Utama"
                                D[Tambah Pengguna Baru];
                                E[Edit Pengguna];
                                F[Non-Aktifkan Pengguna];
                                G[Impor Pengguna Massal];
                            end

                            subgraph "Proses Tambah"
                                H[Isi Form Data Pengguna];
                                I{Validasi Data};
                                J[Simpan Pengguna Baru &<br>Tentukan Role];
                                K[Tampilkan Pesan Error];
                            end

                            subgraph "Proses Edit"
                                L[Pilih Pengguna];
                                M[Ubah Data pada Form];
                                N{Validasi Data Edit};
                                O[Update Data Pengguna];
                                P[Tampilkan Pesan Error Edit];
                            end

                            subgraph "Proses Non-Aktifkan"
                                Q[Pilih Pengguna untuk Non-Aktifkan];
                                R{Konfirmasi};
                                S[Ubah Status jadi 'Suspended',<br>Kosongkan Jabatan,<br>Alihkan Bawahan];
                                T[Pengguna Masuk ke Daftar Arsip];
                            end

                            subgraph "Proses Impor"
                                U[Upload File CSV];
                                V{Validasi Format File};
                                W[Proses Impor Data];
                                X[Tampilkan Pesan Error Impor];
                            end

                            subgraph "Area Arsip"
                                Z[Tampil Daftar Pengguna<br>yang Dinon-Aktifkan];
                                AA[Aktifkan Kembali];
                                AB[Hapus Permanen];
                            end

                            subgraph "Proses Aktifkan Kembali"
                                AC[Pilih Pengguna dari Arsip];
                                AD{Konfirmasi Aktivasi};
                                AE[Ubah Status jadi 'Active'];
                            end

                            subgraph "Proses Hapus Permanen"
                                AF[Pilih Pengguna dari Arsip];
                                AG{Konfirmasi Hapus};
                                AH[Hapus Data Pengguna<br>dari Database];
                            end

                            %% Link Definitions
                            A --> B;
                            B --> C;
                            C --> D;
                            C --> E;
                            C --> F;
                            C --> G;
                            C --> Y;

                            D --> H;
                            H --> I;
                            I -- Valid --> J;
                            I -- Tidak Valid --> K;
                            J --> C;
                            K --> H;

                            E --> L;
                            L --> M;
                            M --> N;
                            N -- Valid --> O;
                            N -- Tidak Valid --> P;
                            O --> C;
                            P --> M;

                            F --> Q;
                            Q --> R;
                            R -- Ya --> S;
                            R -- Tidak --> C;
                            S --> T;
                            T --> Z;

                            G --> U;
                            U --> V;
                            V -- Valid --> W;
                            V -- Tidak Valid --> X;
                            W --> C;
                            X --> G;

                            Y -- Ya --> Z;
                            Y -- Tidak --> End;

                            Z --> AA;
                            Z --> AB;

                            AA --> AC;
                            AC --> AD;
                            AD -- Ya --> AE;
                            AD -- Tidak --> Z;
                            AE --> C;

                            AB --> AF;
                            AF --> AG;
                            AG -- Ya --> AH;
                            AG -- Tidak --> Z;
                            AH --> Z;

                            %% Styling
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
