<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            <i class="fas fa-swimmer mr-2"></i>
            {{ __('Alur Kerja Resource Pool') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">

            <x-card>
                <div class="p-6">
                    <h3 class="text-xl font-bold text-gray-800 mb-2">Dokumentasi Alur Kerja Resource Pool</h3>
                    <p class="text-gray-600">Halaman ini menjelaskan proses pengelolaan *resource pool*, yaitu mekanisme untuk meminjamkan pegawai antar unit kerja untuk kebutuhan proyek atau tugas khusus.</p>
                </div>
            </x-card>

            <x-card>
                <div class="p-6">
                    <h3 class="text-xl font-bold text-gray-800 mb-4 border-b pb-2">Flowchart Alur Kerja</h3>
                    <p class="text-gray-600 mb-6">Flowchart ini merinci keseluruhan proses, dari memasukkan anggota ke dalam pool hingga proses peminjaman oleh unit lain.</p>
                    <div class="p-4 bg-gray-50 rounded-lg text-center">
                        <pre class="mermaid">
graph TD
    classDef page fill:#EBF5FB,stroke:#3498DB,color:#2874A6,stroke-width:1px;
    classDef action fill:#FEF9E7,stroke:#F1C40F,color:#B7950B,stroke-width:1px;
    classDef process fill:#E8F8F5,stroke:#1ABC9C,color:#148F77,stroke-width:1px;
    classDef decision fill:#FDEDEC,stroke:#C0392B,color:#A93226,stroke-width:1px;
    classDef api fill:#F4ECF7,stroke:#8E44AD,color:#6C3483,stroke-width:1px;

    subgraph "A. Manajemen Internal Pool"
        A1["<i class='fa fa-user-tie'></i> Manajer/Pimpinan Unit"]:::action;
        A2["<i class='fa fa-users'></i> Halaman Resource Pool"]:::page;
        A3["<i class='fa fa-toggle-on'></i> Toggle Status 'Masuk Pool'"]:::action;
        A4["<i class='fa fa-pen'></i> Isi Catatan Ketersediaan"]:::action;
        A5["<i class='fa fa-save'></i> Simpan Perubahan"]:::process;
        A6["<i class='fa fa-check-circle'></i> Pegawai Tersedia di Pool"]:::process;
    end

    subgraph "B. Pemanfaatan oleh Unit Lain"
        B1["<i class='fa fa-user-tie'></i> Manajer Proyek (Peminjam)"]:::action;
        B2["<i class='fa fa-tasks'></i> Form Tambah Tim Proyek"]:::page;
        B3["<i class='fa fa-cogs'></i> Panggil API<br>getAvailableMembers()"]:::api;
        B4["<i class='fa fa-list'></i> Tampilkan Daftar<br>Anggota Pool yang Tersedia"]:::process;
        B5["<i class='fa fa-mouse-pointer'></i> Pilih Anggota & Ajukan"]:::action;
    end

    subgraph "C. Alur Permintaan Peminjaman"
        C1["<i class='fa fa-file-alt'></i> Buat PeminjamanRequest"]:::process;
        C2["<i class='fa fa-bell'></i> Notifikasi ke<br>Atasan Anggota Pool"]:::process;
        C3["<i class='fa fa-user-check'></i> Atasan Memberi Persetujuan"]:::action;
        C4{Disetujui?}:::decision;
        C5["<i class='fa fa-check'></i> Disetujui<br>Anggota masuk tim proyek"]:::process;
        C6["<i class='fa fa-times'></i> Ditolak"]:::process;
    end

    %% --- Menghubungkan Alur ---
    A1 --> A2;
    A2 --> A3;
    A3 --> A4;
    A4 --> A5;
    A5 --> A6;

    B1 --> B2;
    B2 --> B3;
    B3 --> B4;
    B4 --> B5;
    B5 --> C1;

    C1 --> C2;
    C2 --> C3;
    C3 --> C4;
    C4 -- Ya --> C5;
    C4 -- Tidak --> C6;

                        </pre>
                    </div>
                </div>
            </x-card>

            <x-card>
                <div class="p-6">
                    <h3 class="text-xl font-bold text-gray-800 mb-4 border-b pb-2">Deskripsi Alur Kerja</h3>
                    <div class="prose max-w-none text-gray-700 space-y-4">
                        <div>
                            <h4 class="font-semibold text-gray-800">1. Manajemen Internal Pool (Oleh Pimpinan Unit)</h4>
                            <p>Seorang pimpinan unit (Eselon, Koordinator) dapat mengelola status keanggotaan *resource pool* untuk para bawahannya.</p>
                            <ul class="list-disc list-inside ml-4 space-y-2">
                                <li><strong>Halaman Resource Pool</strong>: Pimpinan mengakses menu ini untuk melihat daftar bawahan beserta persentase beban kerja mereka saat ini.</li>
                                <li><strong>Menambahkan ke Pool</strong>: Dengan mengaktifkan tombol *toggle* "Masuk Pool", pimpinan menandai bahwa seorang pegawai sedang tidak memiliki banyak beban kerja dan dapat "dipinjam" oleh unit lain.</li>
                                <li><strong>Catatan Ketersediaan</strong>: Pimpinan dapat menambahkan catatan spesifik, misalnya "Tersedia setelah tanggal XX" atau "Hanya untuk tugas analisis".</li>
                            </ul>
                        </div>
                        <div>
                            <h4 class="font-semibold text-gray-800">2. Proses Peminjaman (Oleh Manajer Proyek)</h4>
                            <p>Ketika unit lain membutuhkan sumber daya tambahan untuk sebuah proyek, mereka dapat memanfaatkan *resource pool*.</p>
                             <ul class="list-disc list-inside ml-4 space-y-2">
                                <li><strong>Pencarian Anggota</strong>: Saat akan menugaskan anggota ke sebuah proyek, sistem akan menyediakan opsi untuk mencari dari *resource pool*.</li>
                                <li><strong>API Call</strong>: Sistem memanggil API di belakang layar untuk mendapatkan daftar semua pegawai yang statusnya `is_in_resource_pool = true`.</li>
                                <li><strong>Pengajuan Permintaan</strong>: Setelah memilih anggota yang diinginkan, Manajer Proyek mengajukan permintaan peminjaman.</li>
                            </ul>
                        </div>
                        <div>
                            <h4 class="font-semibold text-gray-800">3. Alur Persetujuan Peminjaman</h4>
                            <p>Peminjaman pegawai tidak terjadi secara otomatis, melainkan memerlukan persetujuan dari atasan pegawai yang bersangkutan untuk memastikan tidak ada konflik jadwal atau prioritas.</p>
                             <ul class="list-disc list-inside ml-4 space-y-2">
                                <li><strong>Permintaan Formal</strong>: Sistem membuat record `PeminjamanRequest` yang berisi detail siapa yang meminjam, siapa yang dipinjam, dan untuk keperluan apa.</li>
                                <li><strong>Notifikasi & Persetujuan</strong>: Atasan dari pegawai yang akan dipinjam akan menerima notifikasi dan harus memberikan persetujuan (`Approve` atau `Reject`).</li>
                                <li><strong>Hasil</strong>: Jika disetujui, pegawai tersebut secara resmi menjadi bagian dari tim proyek peminjam. Jika ditolak, proses berhenti.</li>
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
