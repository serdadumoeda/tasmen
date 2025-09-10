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

            <x-card>
                <div class="p-6">
                    <h3 class="text-xl font-bold text-gray-800 mb-2">Dokumentasi Alur Kerja Pencatatan Surat</h3>
                    <p class="text-gray-600">Halaman ini menjelaskan alur kerja utama untuk modul Surat. Proses ini berfokus pada bagaimana sebuah surat yang sudah ada (misalnya, surat fisik yang dipindai atau surat digital dari eksternal) dicatat ke dalam sistem untuk kemudian ditindaklanjuti melalui disposisi atau dijadikan dasar penugasan.</p>
                </div>
            </x-card>

            <x-card>
                <div class="p-6">
                    <h3 class="text-xl font-bold text-gray-800 mb-4 border-b pb-2">Flowchart Alur Kerja</h3>
                    <p class="text-gray-600 mb-6">Flowchart ini merinci langkah-langkah dari pengunggahan hingga tindak lanjut surat, meniru sintaks yang sudah terbukti berjalan.</p>
                    <div class="p-4 bg-gray-50 rounded-lg text-center">
                        <pre class="mermaid">
graph TD
    classDef page fill:#EBF5FB,stroke:#3498DB,color:#2874A6,stroke-width:1px;
    classDef action fill:#FEF9E7,stroke:#F1C40F,color:#B7950B,stroke-width:1px;
    classDef process fill:#E8F8F5,stroke:#1ABC9C,color:#148F77,stroke-width:1px;
    classDef decision fill:#FDEDEC,stroke:#C0392B,color:#A93226,stroke-width:1px;
    classDef end fill:#F2F3F4,stroke:#99A3A4,color:#616A6B,stroke-width:1px;

    subgraph "Tahap 1: Pencatatan"
        A1["<i class='fa fa-user'></i> Pengguna"]:::action -- Klik 'Unggah Surat Baru' --> A2["<i class='fa fa-upload'></i> Form Unggah Surat"]:::page;
        A2 -- Isi Form --> A3{"<i class='fa fa-check-double'></i> Validasi Sistem"}:::decision;
        A3 -- Gagal --> A2;
        A3 -- Sukses --> A4["<i class='fa fa-save'></i> Surat Tercatat<br>Status: 'Draft'"]:::process;
    end

    subgraph "Tahap 2: Tindak Lanjut"
        B1["<i class='fa fa-file-alt'></i> Buka Halaman Detail Surat"]:::page;
        B1 --> B2{"<i class='fa fa-question-circle'></i> Perlu Tindak Lanjut?"}:::decision;
        B2 -- Ya --> B3["<i class='fa fa-random'></i> Pilih Aksi"]:::action;
        B3 -- Buat Disposisi --> B4["<i class='fa fa-paper-plane'></i> Sistem membuat disposisi"]:::process;
        B3 -- Jadikan Tugas --> B5["<i class='fa fa-tasks'></i> Sistem membuat tugas baru"]:::process;
    end

    subgraph "Tahap 3: Perubahan Status & Selesai"
        B4 --> C1["Status Surat diubah menjadi 'Dikirim'"]:::process;
        B5 --> C2["Status Surat diubah menjadi 'Disetujui'"]:::process;
        B2 -- Tidak --> C3["<i class='fa fa-archive'></i> Surat selesai diproses<br>dan siap diarsipkan"]:::end;
        C1 --> C3;
        C2 --> C3;
    end

    A4 --> B1;
                        </pre>
                    </div>
                </div>
            </x-card>

            <x-card>
                <div class="p-6">
                    <h3 class="text-xl font-bold text-gray-800 mb-4 border-b pb-2">Deskripsi Detail Alur Kerja</h3>
                    <div class="prose max-w-none text-gray-700 space-y-4">
                        <div>
                            <h4 class="font-semibold text-gray-800">1. Pencatatan Surat</h4>
                            <p>Proses dimulai ketika seorang pengguna (staf atau pimpinan) perlu mencatat surat yang sudah ada ke dalam sistem untuk keterlacakan dan tindak lanjut digital.</p>
                            <ul class="list-disc list-inside ml-4 space-y-2">
                                <li><strong>Input Data</strong>: Pengguna menekan tombol "Unggah Surat Baru", kemudian mengisi informasi dasar seperti Perihal dan Tanggal Surat, serta mengunggah dokumen digital (PDF, Word, dll).</li>
                                <li><strong>Status Awal</strong>: Setelah berhasil diunggah, surat akan tercatat di sistem dengan status awal <strong>'Draft'</strong>. Pada tahap ini, surat hanya menjadi catatan digital dan belum ditindaklanjuti.</li>
                            </ul>
                        </div>
                        <div>
                            <h4 class="font-semibold text-gray-800">2. Proses Tindak Lanjut dari Detail Surat</h4>
                            <p>Dari halaman Detail Surat, pengguna dapat melakukan beberapa aksi utama untuk menindaklanjuti surat tersebut.</p>
                            <ul class="list-disc list-inside ml-4 space-y-2">
                                <li><strong>Disposisi</strong>: Jika surat perlu diketahui atau ditindaklanjuti oleh pihak lain, pengguna dapat membuat disposisi. Aksi ini akan mengubah status surat menjadi <strong>'Dikirim'</strong>, menandakan surat sedang dalam proses sirkulasi atau disposisi.</li>
                                <li><strong>Menjadikan Tugas</strong>: Jika surat tersebut memerlukan sebuah pekerjaan konkret dengan output yang jelas, pengguna dapat langsung membuat tugas baru dari surat tersebut. Aksi ini akan mengubah status surat menjadi <strong>'Disetujui'</strong>, yang menandakan bahwa isi surat telah disetujui dan kini menjadi dasar untuk sebuah pekerjaan. File surat juga akan otomatis terlampir pada tugas yang baru dibuat.</li>
                                <li><strong>Tanpa Tindak Lanjut</strong>: Jika surat hanya bersifat informasional dan tidak memerlukan aksi lebih lanjut, pengguna tidak perlu melakukan apa-apa. Surat akan tetap tersimpan di sistem.</li>
                            </ul>
                        </div>
                        <div>
                            <h4 class="font-semibold text-gray-800">3. Pengarsipan Otomatis</h4>
                            <p>Setelah surat ditindaklanjuti (statusnya menjadi 'Dikirim' atau 'Disetujui'), atau jika tidak ada tindak lanjut sama sekali, surat tersebut secara otomatis dianggap sebagai arsip digital. Pengguna dapat menemukannya kembali melalui modul Arsip Digital untuk keperluan di masa depan.</p>
                        </div>
                    </div>
                </div>
            </x-card>

        </div>
    </div>

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/mermaid@10.3.1/dist/mermaid.min.js"></script>
        <script>
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
