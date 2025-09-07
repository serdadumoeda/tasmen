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
                    <p class="text-gray-600 mb-6">Flowchart ini merinci bagaimana pengguna berinteraksi dengan arsip dan mengelola berkas virtual mereka.</p>
                    <div class="p-4 bg-gray-50 rounded-lg text-center">
                        <pre class="mermaid">
graph TD
    classDef page fill:#EBF5FB,stroke:#3498DB,color:#2874A6,stroke-width:1px;
    classDef action fill:#FEF9E7,stroke:#F1C40F,color:#B7950B,stroke-width:1px;
    classDef process fill:#E8F8F5,stroke:#1ABC9C,color:#148F77,stroke-width:1px;
    classDef decision fill:#FDEDEC,stroke:#C0392B,color:#A93226,stroke-width:1px;
    classDef data fill:#FADBD8,stroke:#E74C3C,color:#B03A2E,stroke-width:1px;

    subgraph "A. Pencarian & Penemuan"
        A1["<i class='fa fa-user'></i> Pengguna"]:::action --> A2["<i class='fa fa-archive'></i> Halaman Arsip Digital"]:::page;
        A2 --> A3["<i class='fa fa-search'></i> Gunakan Filter<br>(Keyword, Tanggal, Jenis, Klasifikasi)"]:::action;
        A3 --> A4["<i class='fa fa-list'></i> Tampilkan Hasil Pencarian"]:::page;
        A4 --> A5["<i class='fa fa-check-square'></i> Pilih Satu atau Lebih Surat"]:::action;
    end

    subgraph "B. Pengelolaan Berkas Virtual"
        B1["<i class='fa fa-folder-plus'></i> Buat Berkas Baru"]:::action;
        B1 -- Nama & Deskripsi --> B2["<i class='fa fa-save'></i> Simpan Berkas<br>(Milik Pengguna)"]:::process;
        B2 --> B3["<i class='fa fa-folder'></i> Berkas Tersedia di Dropdown"]:::page;
    end

    subgraph "C. Pengelompokan Surat"
        C1["<i class='fa fa-folder-open'></i> Pilih Berkas Tujuan<br>dari Dropdown"]:::action;
        C2["<i class='fa fa-share-square'></i> Klik 'Tambahkan ke Berkas'"]:::action;
        C3["<i class='fa fa-link'></i> Sistem Membuat Relasi<br>Surat <--> Berkas"]:::process;
        C4["<i class='fa fa-info-circle'></i> Tampilkan Pesan Sukses"]:::process;
    end

    A2 --> B1;
    A5 --> C1;
    C1 --> C2;
    C2 --> C3;
    C3 --> C4;
    C4 --> A2;

    subgraph "Sumber Data"
        S1["Database Surat Masuk"]:::data;
        S2["Database Surat Keluar"]:::data;
        S1 -- Status 'Disetujui'/'Diarsipkan' --> A2;
        S2 -- Status 'Disetujui'/'Diarsipkan' --> A2;
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
                            <h4 class="font-semibold text-gray-800">1. Pusat Pencarian Surat</h4>
                            <p>Modul Arsip Digital berfungsi sebagai pusat pencarian untuk semua surat (masuk dan keluar) yang telah menyelesaikan siklusnya (berstatus 'Disetujui' atau 'Diarsipkan').</p>
                            <ul class="list-disc list-inside ml-4 space-y-2">
                                <li><strong>Pencarian Komprehensif</strong>: Pengguna dapat dengan mudah menemukan surat berdasarkan kata kunci, rentang tanggal, jenis surat (masuk/keluar), atau kode klasifikasi.</li>
                                <li><strong>Akses Terpadu</strong>: Ini menghilangkan kebutuhan untuk mencari di dua modul yang terpisah, menyediakan satu sumber kebenaran untuk semua korespondensi yang telah selesai.</li>
                            </ul>
                        </div>
                        <div>
                            <h4 class="font-semibold text-gray-800">2. Berkas Virtual Pribadi</h4>
                            <p>Fitur utama dari modul ini adalah kemampuan pengguna untuk membuat "berkas" atau folder virtual mereka sendiri. Ini adalah sistem pengarsipan pribadi di atas arsip umum.</p>
                             <ul class="list-disc list-inside ml-4 space-y-2">
                                <li><strong>Pembuatan Berkas</strong>: Pengguna dapat membuat berkas dengan nama apa pun yang mereka inginkan (misalnya, "Surat Undangan Rapat 2024" atau "SK Terkait Proyek X").</li>
                                <li><strong>Pengelompokan Surat</strong>: Setelah menemukan surat-surat yang relevan melalui pencarian, pengguna dapat memilih beberapa surat sekaligus dan memasukkannya ke dalam berkas yang sesuai.</li>
                                <li><strong>Relasi Fleksibel</strong>: Sebuah surat dapat dimasukkan ke dalam beberapa berkas yang berbeda, dan sebuah berkas dapat berisi banyak surat.</li>
                             </ul>
                        </div>
                         <div>
                            <h4 class="font-semibold text-gray-800">3. Tujuan & Manfaat</h4>
                             <ul class="list-disc list-inside ml-4 space-y-2">
                                <li><strong>Temu Kembali Informasi</strong>: Mempercepat proses penemuan kembali surat-surat penting yang saling terkait, bahkan jika surat-surat tersebut dibuat pada waktu yang berbeda dan oleh orang yang berbeda.</li>
                                <li><strong>Pengarsipan Kontekstual</strong>: Memungkinkan pengguna untuk mengelompokkan surat berdasarkan konteks atau subjek tertentu yang relevan dengan pekerjaan mereka, bukan hanya berdasarkan urutan kronologis.</li>
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
