<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            <i class="fas fa-network-wired mr-2"></i>
            {{ __('Alur Kerja Manajemen Alur Persetujuan') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">

            <x-card>
                <div class="p-6">
                    <h3 class="text-xl font-bold text-gray-800 mb-2">Dokumentasi Alur Persetujuan</h3>
                    <p class="text-gray-600">Halaman ini menjelaskan cara membuat dan mengelola alur persetujuan berjenjang yang dapat diterapkan pada berbagai modul (seperti Cuti) di seluruh unit kerja.</p>
                </div>
            </x-card>

            <x-card>
                <div class="p-6">
                    <h3 class="text-xl font-bold text-gray-800 mb-4 border-b pb-2">Flowchart Alur Kerja</h3>
                    <p class="text-gray-600 mb-6">Flowchart ini merinci proses pembuatan alur persetujuan dan bagaimana alur tersebut digunakan oleh sistem.</p>
                    <div class="p-4 bg-gray-50 rounded-lg text-center">
                        <pre class="mermaid">
graph TD
    classDef page fill:#EBF5FB,stroke:#3498DB,color:#2874A6,stroke-width:1px;
    classDef action fill:#FEF9E7,stroke:#F1C40F,color:#B7950B,stroke-width:1px;
    classDef process fill:#E8F8F5,stroke:#1ABC9C,color:#148F77,stroke-width:1px;
    classDef decision fill:#FDEDEC,stroke:#C0392B,color:#A93226,stroke-width:1px;
    classDef link fill:#D5F5E3,stroke:#27AE60,color:#1E8449,stroke-width:1px;

    subgraph "A. Konfigurasi oleh Admin"
        A1["<i class='fa fa-user-shield'></i> Admin"]:::action --> A2["<i class='fa fa-list-alt'></i> Halaman Daftar Alur"]:::page;
        A2 -->|Klik 'Buat Alur'| A3["<i class='fa fa-keyboard'></i> Isi Nama & Deskripsi"]:::page;
        A3 --> A4["<i class='fa fa-save'></i> Simpan Alur (Wadah)"]:::process;
        A4 --> A5["<i class='fa fa-tasks'></i> Halaman Detail Alur"]:::page;
        A5 --> A6["<i class='fa fa-plus-circle'></i> Tambah Langkah Persetujuan"]:::action;
        A6 --> A7["<i class='fa fa-user-tag'></i> Pilih Role & Urutan"]:::page;
        A7 --> A8["<i class='fa fa-save'></i> Simpan Langkah"]:::process;
        A8 --> A5;
        A5 --> A9["<i class='fa fa-link'></i> Tautkan Alur ke Unit Kerja"]:::link;
    end

    subgraph "B. Penggunaan oleh Sistem (Contoh: Modul Cuti)"
        B1["<i class='fa fa-user'></i> Pegawai Mengajukan Cuti"]:::action;
        B2["<i class='fa fa-cogs'></i> Sistem Mendeteksi<br>Unit Kerja Pegawai"]:::process;
        B3["<i class='fa fa-cogs'></i> Sistem Mengambil<br>Alur Persetujuan yg Tertaut"]:::process;
        B4["<i class='fa fa-bell'></i> Notifikasi ke Role<br>di Langkah Pertama"]:::process;
        B5{Disetujui?}:::decision;
        B5 -- Ya --> B6{Ada Langkah Berikutnya?}:::decision;
        B5 -- Tidak --> B9["<i class='fa fa-times-circle'></i> Permintaan Ditolak"]:::process;
        B6 -- Ya --> B7["<i class='fa fa-bell'></i> Notifikasi ke Role<br>di Langkah Berikutnya"]:::process;
        B6 -- Tidak (Final) --> B8["<i class='fa fa-check-circle'></i> Permintaan Disetujui"]:::process;
        B7 --> B5;
    end

    A9 --> B3;
                        </pre>
                    </div>
                </div>
            </x-card>

            <x-card>
                <div class="p-6">
                    <h3 class="text-xl font-bold text-gray-800 mb-4 border-b pb-2">Deskripsi Alur Kerja</h3>
                    <div class="prose max-w-none text-gray-700 space-y-4">
                        <div>
                            <h4 class="font-semibold text-gray-800">1. Konfigurasi Alur Persetujuan (Oleh Admin)</h4>
                            <p>Admin memiliki kendali penuh untuk mendesain proses persetujuan yang dinamis dan sesuai dengan struktur organisasi.</p>
                            <ul class="list-disc list-inside ml-4 space-y-2">
                                <li><strong>Pembuatan Wadah Alur</strong>: Admin pertama-tama membuat sebuah "wadah" alur kerja, misalnya "Alur Cuti Jabatan Fungsional" atau "Alur Peminjaman Staf Eselon II".</li>
                                <li><strong>Penambahan Langkah</strong>: Di dalam setiap wadah, Admin menambahkan satu atau lebih langkah persetujuan. Setiap langkah mendefinisikan urutan (`step`) dan peran (`role`) pejabat yang harus memberikan persetujuan. Contoh: Langkah 1, Role: Koordinator; Langkah 2, Role: Eselon II.</li>
                                <li><strong>Penanda Persetujuan Final</strong>: Salah satu langkah dapat ditandai sebagai `is_final_approval` untuk menandakan akhir dari proses persetujuan.</li>
                                <li><strong>Penautan ke Unit Kerja</strong>: Setelah sebuah alur selesai dibuat, alur tersebut harus ditautkan ke satu atau lebih unit kerja melalui halaman edit unit kerja. Ini memungkinkan unit yang berbeda memiliki alur persetujuan yang berbeda pula.</li>
                            </ul>
                        </div>
                        <div>
                            <h4 class="font-semibold text-gray-800">2. Eksekusi Alur oleh Sistem</h4>
                            <p>Ketika seorang pegawai memulai sebuah proses yang membutuhkan persetujuan (misalnya mengajukan cuti), sistem akan secara otomatis mengeksekusi alur yang telah dikonfigurasi.</p>
                             <ul class="list-disc list-inside ml-4 space-y-2">
                                <li><strong>Identifikasi Alur</strong>: Sistem akan memeriksa unit kerja dari pegawai yang bersangkutan dan mencari alur persetujuan yang tertaut pada unit tersebut.</li>
                                <li><strong>Persetujuan Berjenjang</strong>: Sistem akan mengirimkan notifikasi permintaan persetujuan kepada atasan yang memiliki `role` yang cocok dengan langkah pertama dalam alur.</li>
                                <li><strong>Penerusan Otomatis</strong>: Jika atasan di langkah pertama menyetujui, sistem akan otomatis memeriksa apakah ada langkah berikutnya. Jika ada, notifikasi akan diteruskan ke atasan dengan `role` yang sesuai di langkah kedua, dan begitu seterusnya hingga mencapai langkah final.</li>
                                <li><strong>Proses Berhenti</strong>: Jika ada atasan di tingkat manapun yang menolak permintaan, alur akan langsung berhenti dan permintaan dianggap ditolak.</li>
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
