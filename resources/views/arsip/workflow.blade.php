<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            <i class="fas fa-archive mr-2"></i>
            {{ __('Alur Kerja Arsip Digital') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">
            <div class="mb-4">
                <a href="javascript:history.back()" class="inline-flex items-center text-sm font-semibold text-gray-600 hover:text-gray-900">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Kembali
                </a>
            </div>

            <x-card>
                <div class="p-6">
                    <h3 class="text-xl font-bold text-gray-800 mb-2">Dokumentasi Alur Arsip Digital</h3>
                    <p class="text-gray-600">Halaman ini menjelaskan proses penggunaan modul Arsip Digital, yang memungkinkan pengguna untuk mencari dan mengelompokkan surat-surat penting ke dalam berkas virtual pribadi.</p>
                </div>
            </x-card>

            <x-card>
                <div class="p-6">
                    <h3 class="text-xl font-bold text-gray-800 mb-4 border-b pb-2">Flowchart Alur Kerja</h3>
                    <p class="text-gray-600 mb-6">Flowchart ini merinci bagaimana pengguna berinteraksi dengan arsip dan mengelola berkas virtual mereka sesuai dengan logika terbaru.</p>
                    <div class="p-4 bg-gray-50 rounded-lg text-center">
                        <pre class="mermaid">
graph TD
    classDef page fill:#EBF5FB,stroke:#3498DB,color:#2874A6,stroke-width:1px;
    classDef action fill:#FEF9E7,stroke:#F1C40F,color:#B7950B,stroke-width:1px;
    classDef process fill:#E8F8F5,stroke:#1ABC9C,color:#148F77,stroke-width:1px;
    classDef data fill:#FADBD8,stroke:#E74C3C,color:#B03A2E,stroke-width:1px;
    classDef storage fill:#F2F3F4,stroke:#99A3A4,color:#616A6B,stroke-width:1px;

    subgraph "A. Halaman Arsip Utama (Inbox Berkas)"
        A1["<i class='fa fa-user'></i> Pengguna"]:::action --> A2["<i class='fa fa-archive'></i> Halaman Arsip Digital"]:::page;
        A2 -- Menampilkan --> A3["<i class='fa fa-envelope-open'></i> Daftar Surat<br><i>Hanya yang belum diberkaskan</i>"]:::process;
        A3 --> A4["<i class='fa fa-search'></i> Gunakan Filter<br>(Keyword, Tanggal, Klasifikasi)"]:::action;
        A4 --> A3;
        A3 --> A5["<i class='fa fa-check-square'></i> Pilih Satu atau Lebih Surat"]:::action;
    end

    subgraph "B. Pengelolaan & Pengisian Berkas"
        B1["<i class='fa fa-folder-plus'></i> Buat Berkas Baru"]:::action;
        A5 -- Pilih Berkas Tujuan --> B2["<i class='fa fa-folder-open'></i> Dropdown Berkas"]:::page;
        B2 --> B3["<i class='fa fa-share-square'></i> Klik 'Masukkan ke Berkas'"]:::action;
        B3 --> B4["<i class='fa fa-link'></i> Sistem Membuat Relasi<br>Surat <--> Berkas"]:::process;
        B4 --> B5["Surat Hilang dari<br>Daftar Arsip Utama"]:::process;
    end

    subgraph "C. Melihat Isi Berkas"
        C1["<i class='fa fa-folder'></i> Klik Nama Berkas<br>di 'Daftar Berkas Virtual'"]:::action --> C2["<i class='fa fa-file-alt'></i> Halaman Detail Berkas"]:::page;
        C2 -- Menampilkan --> C3["Daftar Surat<br><i>Hanya yang ada di dalam berkas ini</i>"]:::process;
        C3 --> C4["<i class='fa fa-search'></i> Filter di Dalam Berkas<br>(Keyword, Tanggal, Klasifikasi)"]:::action;
        C4 --> C3;
    end

    A2 --> B1;
    A2 --> C1;

    subgraph "Sumber Data"
        S1["Database Surat<br>Status: 'Disetujui'/'Diarsipkan'"]:::data;
        S1 --> A2;
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
                            <h4 class="font-semibold text-gray-800">1. Halaman Arsip Digital: "Inbox" untuk Surat Selesai</h4>
                            <p>Halaman utama Arsip Digital kini berfungsi seperti sebuah "inbox" yang hanya menampilkan surat-surat yang telah menyelesaikan siklusnya (berstatus 'Disetujui' atau 'Diarsipkan') <strong>dan belum dimasukkan ke dalam berkas manapun</strong>.</p>
                            <ul class="list-disc list-inside ml-4 space-y-2">
                                <li><strong>Tampilan Bersih</strong>: Pengguna hanya melihat surat-surat yang memerlukan tindakan pengarsipan, mengurangi kekacauan visual.</li>
                                <li><strong>Pencarian</strong>: Fungsi filter (kata kunci, tanggal, klasifikasi) tetap tersedia untuk memudahkan menemukan surat yang akan diberkaskan.</li>
                            </ul>
                        </div>
                        <div>
                            <h4 class="font-semibold text-gray-800">2. Pemberkasan Virtual</h4>
                            <p>Pengguna dapat membuat "berkas" atau folder virtual pribadi untuk mengelompokkan surat-surat yang saling terkait.</p>
                             <ul class="list-disc list-inside ml-4 space-y-2">
                                <li><strong>Buat Berkas</strong>: Pengguna dapat membuat berkas baru kapan saja dari sidebar (misal: "Undangan Rapat Q3 2024").</li>
                                <li><strong>Masukkan ke Berkas</strong>: Setelah memilih satu atau lebih surat dari daftar "inbox", pengguna memilih berkas tujuan dari dropdown, lalu klik "Masukkan ke Berkas".</li>
                                <li><strong>Surat "Pindah"</strong>: Setelah berhasil dimasukkan, surat tersebut akan hilang dari daftar utama (inbox) dan hanya akan dapat diakses dari dalam berkas tersebut.</li>
                             </ul>
                        </div>
                         <div>
                            <h4 class="font-semibold text-gray-800">3. Mengakses Isi Berkas</h4>
                             <ul class="list-disc list-inside ml-4 space-y-2">
                                <li><strong>Halaman Detail Berkas</strong>: Mengklik nama berkas di sidebar akan membuka halaman baru yang didedikasikan untuk berkas tersebut.</li>
                                <li><strong>Daftar Isi</strong>: Halaman ini akan menampilkan daftar semua surat yang telah dimasukkan ke dalam berkas tersebut.</li>
                                <li><strong>Pencarian Spesifik</strong>: Di dalam halaman detail berkas, pengguna dapat melakukan pencarian dan pemfilteran lebih lanjut yang hanya berlaku untuk surat-surat di dalam berkas itu.</li>
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
