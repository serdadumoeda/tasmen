<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            <i class="fas fa-sitemap mr-2"></i>
            {{ __('Alur Kerja Modul Surat') }}
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
                    <p class="text-gray-600">Halaman ini menjelaskan alur kerja baru yang disederhanakan untuk modul Surat. Konsepnya adalah surat yang sudah jadi diunggah ke dalam sistem untuk kemudian didisposisikan atau dijadikan dasar penugasan.</p>
                </div>
            </x-card>

            <!-- Flowchart -->
            <x-card>
                <div class="p-6">
                    <h3 class="text-xl font-bold text-gray-800 mb-4 border-b pb-2">Flowchart Alur Kerja</h3>
                    <div class="p-4 bg-gray-50 rounded-lg text-center">
                        <pre class="mermaid">
graph TD
    classDef page fill:#EBF5FB,stroke:#3498DB,color:#2874A6,stroke-width:1px;
    classDef action fill:#FEF9E7,stroke:#F1C40F,color:#B7950B,stroke-width:1px;
    classDef process fill:#E8F8F5,stroke:#1ABC9C,color:#148F77,stroke-width:1px;
    classDef decision fill:#FDEDEC,stroke:#C0392B,color:#A93226,stroke-width:1px;
    classDef end fill:#F2F3F4,stroke:#99A3A4,color:#616A6B,stroke-width:1px;

    A["<i class='fa fa-user'></i> Pengguna"]:::action -->|Klik 'Unggah Surat Baru'| B["<i class='fa fa-upload'></i> Form Unggah Surat"]:::page;
    B -- Isi Perihal, Tanggal &<br>Upload File --> C{<i class='fa fa-check-double'></i> Validasi}:::decision;
    C -- Gagal --> B;
    C -- Sukses --> D["<i class='fa fa-save'></i> Surat Tercatat<br>Status: 'Draft'"]:::process;
    D --> E["<i class='fa fa-file-alt'></i> Halaman Detail Surat"]:::page;
    E --> F{<i class='fa fa-question-circle'></i> Perlu Tindak Lanjut?}:::decision;
    F -- Ya --> G["<i class='fa fa-random'></i> Pilih Aksi"];
    G --> H["<i class='fa fa-paper-plane'></i> Buat Disposisi"]:::action;
    G --> I["<i class='fa fa-tasks'></i> Jadikan Tugas"]:::action;
    H --> J["Surat Berstatus 'Dikirim'"]:::process;
    I --> K["Surat Berstatus 'Disetujui'"]:::process;
    J --> L["<i class='fa fa-archive'></i> Surat Diarsipkan"]:::end;
    K --> L;
    F -- Tidak --> L;
                        </pre>
                    </div>
                </div>
            </x-card>

            <!-- Penjelasan Detail -->
            <x-card>
                <div class="p-6">
                    <h3 class="text-xl font-bold text-gray-800 mb-4 border-b pb-2">Penjelasan Detail Alur Kerja</h3>
                    <div class="prose max-w-none text-gray-700 space-y-4">
                        <div>
                            <h4 class="font-semibold text-gray-800">1. Pencatatan Surat</h4>
                            <p>Proses dimulai ketika seorang pengguna perlu mencatat surat yang sudah ada (misalnya, surat fisik yang dipindai atau surat digital yang diterima dari luar sistem). Mereka mengunggah dokumen ini ke dalam aplikasi.</p>
                            <ul class="list-disc list-inside ml-4 space-y-2">
                                <li><strong>Input Data</strong>: Pengguna mengisi informasi dasar seperti Perihal dan Tanggal Surat, lalu memilih dan mengunggah file dokumen utama.</li>
                                <li><strong>Pencatatan</strong>: Setelah berhasil diunggah, surat akan tercatat di sistem dengan status awal 'Draft' dan dapat dilihat di Daftar Surat.</li>
                            </ul>
                        </div>
                        <div>
                            <h4 class="font-semibold text-gray-800">2. Tindak Lanjut</h4>
                            <p>Dari halaman Detail Surat, pengguna dapat melakukan beberapa aksi untuk menindaklanjuti surat tersebut.</p>
                            <ul class="list-disc list-inside ml-4 space-y-2">
                                <li><strong>Disposisi</strong>: Jika surat perlu diketahui atau ditindaklanjuti oleh pihak lain, pengguna dapat membuat disposisi. Ini akan mengubah status surat menjadi 'Dikirim'.</li>
                                <li><strong>Menjadikan Tugas</strong>: Jika surat tersebut memerlukan sebuah pekerjaan konkret, pengguna dapat langsung membuat tugas baru dari surat tersebut. Ini akan mengubah status surat menjadi 'Disetujui' dan menautkan tugas ke surat asalnya untuk keterlacakan.</li>
                            </ul>
                        </div>
                        <div>
                            <h4 class="font-semibold text-gray-800">3. Pengarsipan</h4>
                            <p>Setelah semua proses tindak lanjut selesai (atau jika tidak ada tindak lanjut yang diperlukan), surat secara otomatis berfungsi sebagai arsip digital. Status akhirnya ('Dikirim', 'Disetujui', atau 'Diarsipkan' secara manual) menandakan bahwa surat telah selesai diproses.</p>
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
