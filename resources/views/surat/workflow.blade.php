<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            <i class="fas fa-sitemap mr-2"></i>
            {{ __('Alur Kerja Lengkap Modul Surat') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">
            <div class="mb-4">
                <a href="{{ route('surat.index') }}" class="inline-flex items-center text-sm font-semibold text-gray-600 hover:text-gray-900">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Kembali ke Daftar Surat
                </a>
            </div>

            <!-- Intro Card -->
            <x-card>
                <div class="p-6">
                    <h3 class="text-xl font-bold text-gray-800 mb-2">Dokumentasi Alur Kerja Surat</h3>
                    <p class="text-gray-600">Halaman ini berisi dokumentasi lengkap mengenai alur kerja Modul Surat, mulai dari pencatatan, disposisi, hingga fitur-fitur terkait lainnya. Gunakan ini sebagai panduan untuk memahami cara kerja sistem.</p>
                </div>
            </x-card>

            <!-- Flowchart Umum -->
            <x-card>
                <div class="p-6">
                    <h3 class="text-xl font-bold text-gray-800 mb-4 border-b pb-2">1. Flowchart Alur Kerja Utama</h3>
                    <p class="text-gray-600 mb-6">Flowchart ini merinci keseluruhan alur kerja modul surat, mulai dari pencatatan surat baru hingga interaksi di halaman detail.</p>
                    <div class="p-4 bg-gray-50 rounded-lg text-center">
                        <pre class="mermaid">
graph TD
    classDef page fill:#EBF5FB,stroke:#3498DB,color:#2874A6,stroke-width:1px;
    classDef action fill:#FEF9E7,stroke:#F1C40F,color:#B7950B,stroke-width:1px;
    classDef process fill:#E8F8F5,stroke:#1ABC9C,color:#148F77,stroke-width:1px;
    classDef decision fill:#FDEDEC,stroke:#C0392B,color:#A93226,stroke-width:1px;

    subgraph "A. Pencatatan & Navigasi"
        A1["<i class='fa fa-list-alt'></i> Halaman Daftar Surat"]:::page -->|Klik 'Unggah Surat'| A2["<i class='fa fa-upload'></i> Form Unggah Surat"]:::page;
        A2 -- Isi Form & Submit --> A3{<i class='fa fa-check-double'></i> Validasi}:::decision;
        A3 -- Gagal --> A2;
        A3 -- Sukses --> A4["<i class='fa fa-save'></i> Simpan Surat (status: draft)"]:::process;
        A4 --> A1;
        A1 -->|Klik Perihal Surat| B_Flow["<i class='fa fa-eye'></i> B. Alur Detail & Aksi Surat"];
    end

    subgraph B_Flow [B. Alur Detail & Aksi Surat]
        B1["<i class='fa fa-file-alt'></i> Halaman Detail Surat"]:::page --> B2["<i class='fa fa-paper-plane'></i> <b>Fitur Utama: Disposisi</b>"]:::action;
        B1 --> B3["<i class='fa fa-tasks'></i> Buat Tugas Harian"]:::action;
        B1 --> B6["<i class='fa fa-folder-plus'></i> Buat Kegiatan Baru"]:::action;
        B1 --> B7["<i class='fa fa-archive'></i> Arsipkan / Pindahkan"]:::action;
        B1 --> B4["<i class='fa fa-download'></i> Unduh File"]:::action;
        B1 --> B5["<i class='fa fa-trash'></i> Hapus Surat"]:::action;
        B2 --> C_Flow["<i class='fa fa-share-alt'></i> C. Alur Disposisi"];
        B3 --> D_Flow["<i class='fa fa-briefcase'></i> D. Alur Konversi ke Tugas"];
        B6 --> E_Flow["<i class='fa fa-project-diagram'></i> E. Alur Konversi ke Kegiatan"];
        B7 --> F_Flow["<i class='fa fa-folder-open'></i> F. Alur Pengarsipan"];
    end

    subgraph F_Flow [F. Alur Pengarsipan]
        direction LR
        F1["Klik 'Arsipkan'/'Pindahkan'"]:::action --> F2["Modal Pilih Berkas"]:::modal;
        F2 --> F3["Pilih Berkas & Simpan"]:::action;
        F3 --> F4["Sistem mengubah<br>surat.berkas_id &<br>surat.status"]:::process;
    end
                        </pre>
                    </div>
                </div>
            </x-card>

            <!-- Flowchart Fokus Disposisi -->
            <x-card>
                <div class="p-6">
                    <h3 class="text-xl font-bold text-gray-800 mb-4 border-b pb-2">2. Flowchart Fokus: Alur Disposisi</h3>
                    <p class="text-gray-600 mb-6">Disposisi adalah inti dari modul surat, memungkinkan surat untuk diteruskan secara berjenjang dengan instruksi. Flowchart ini memetakan proses tersebut.</p>
                    <div class="p-4 bg-gray-50 rounded-lg text-center">
                        <pre class="mermaid">
graph TD
    classDef page fill:#EBF5FB,stroke:#3498DB,color:#2874A6;
    classDef action fill:#FEF9E7,stroke:#F1C40F,color:#B7950B;
    classDef process fill:#E8F8F5,stroke:#1ABC9C,color:#148F77;
    classDef decision fill:#FDEDEC,stroke:#C0392B,color:#A93226;

    A["<i class='fa fa-file-alt'></i> Halaman Detail Surat"]:::page --> B["<i class='fa fa-plus-circle'></i> Klik 'Buat Disposisi'"]:::action;
    B --> C["<i class='fa fa-keyboard'></i> Form Disposisi"]:::page;
    C -- Isi Form (Penerima, Tembusan, Instruksi) --> D{<i class='fa fa-check-double'></i> Validasi Data}:::decision;
    D -- Gagal --> C;
    D -- Sukses --> E{"<i class='fa fa-cogs'></i> DisposisiController@store"}:::process;
    E --> F["<i class='fa fa-save'></i> Simpan Disposisi Baru"]:::process;
    F --> G["<i class='fa fa-bell'></i> Kirim Notifikasi ke Penerima & Tembusan"]:::process;
    F --> H["<i class='fa fa-edit'></i> Update Status Surat menjadi 'Dikirim'"]:::process;
    H --> I["<i class='fa fa-sync-alt'></i> Refresh Halaman Detail"]:::process;
    I --> A;

    subgraph "Hirarki Disposisi"
        J["Disposisi A (Penerima: User 1)"];
        J --> K["User 1 membuat disposisi balasan<br>(Penerima: User 2)"];
        K --> L["Disposisi B (child dari A)"];
    end

    A --> M["<i class='fa fa-search'></i> Lihat Riwayat Disposisi"]:::action;
    M --> N["<i class='fa fa-stream'></i> Tampilan Hirarki Disposisi"]:::page;
                        </pre>
                    </div>
                </div>
            </x-card>

            <!-- Flowchart Fitur Tambahan -->
            <x-card>
                <div class="p-6">
                    <h3 class="text-xl font-bold text-gray-800 mb-4 border-b pb-2">3. Flowchart Konversi Surat</h3>
                    <p class="text-gray-600 mb-6">Surat dapat dikonversi menjadi item yang dapat ditindaklanjuti seperti Tugas Harian atau Kegiatan baru.</p>
                    <div class="p-4 bg-gray-50 rounded-lg text-center">
                        <pre class="mermaid">
graph TD
    classDef page fill:#EBF5FB,stroke:#3498DB,color:#2874A6;
    classDef action fill:#FEF9E7,stroke:#F1C40F,color:#B7950B;
    classDef process fill:#E8F8F5,stroke:#1ABC9C,color:#148F77;

    subgraph D_Flow [D. Alur Konversi ke Tugas Harian]
        D1["<i class='fa fa-file-alt'></i> Halaman Detail Surat"]:::page --> D2["<i class='fa fa-tasks'></i> Klik 'Buat Tugas'"]:::action;
        D2 --> D3{"<i class='fa fa-cogs'></i> SuratController@makeTask"}:::process;
        D3 --> D4["<i class='fa fa-edit'></i> Update Status Surat menjadi 'Disetujui'"]:::process;
        D3 --> D5["<i class='fa fa-briefcase'></i> Buat Task Baru (project_id = null)"]:::process;
        D5 --> D6["<i class='fa fa-arrow-right'></i> Redirect ke Halaman Edit Tugas"]:::page;
    end

    subgraph E_Flow [E. Alur Konversi ke Kegiatan]
        E1["<i class='fa fa-file-alt'></i> Halaman Detail Surat"]:::page --> E2["<i class='fa fa-folder-plus'></i> Klik 'Buat Kegiatan'"]:::action;
        E2 --> E3{"<i class='fa fa-cogs'></i> SuratController@makeProject"}:::process;
        E3 --> E4["<i class='fa fa-share-square'></i> Redirect ke Form Buat Kegiatan<br>dengan data terisi (nama, surat_id)"]:::page;
        E4 -- Isi form & submit --> E5{"<i class='fa fa-cogs'></i> ProjectController@storeStep1"}:::process;
        E5 --> E6["<i class='fa fa-link'></i> Simpan Kegiatan &<br>kaitkan dengan Surat"]:::process;
        E6 --> E7["<i class='fa fa-arrow-right'></i> Lanjut ke Langkah 2 (Tim)"]:::page;
    end
                        </pre>
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
