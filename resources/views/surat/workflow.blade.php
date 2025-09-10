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
                    <p class="text-gray-600">Halaman ini menjelaskan alur kerja utama untuk modul Surat. Proses ini berfokus pada bagaimana sebuah surat yang sudah ada (misalnya, surat fisik yang dipindai atau surat digital dari eksternal) dicatat ke dalam sistem untuk kemudian ditindaklanjuti.</p>
                </div>
            </x-card>

            <x-card>
                <div class="p-6">
                    <h3 class="text-xl font-bold text-gray-800 mb-4 border-b pb-2">Flowchart Alur Kerja (Sederhana)</h3>
                    <p class="text-gray-600 mb-6">Untuk menghindari bug rendering pada diagram yang kompleks, alur kerja disajikan dalam bentuk yang lebih ringkas dan linear.</p>
                    <div class="p-4 bg-gray-50 rounded-lg text-center">
                        <pre class="mermaid">
graph TD
    classDef page fill:#EBF5FB,stroke:#3498DB,color:#2874A6,stroke-width:1px;
    classDef action fill:#FEF9E7,stroke:#F1C40F,color:#B7950B,stroke-width:1px;
    classDef process fill:#E8F8F5,stroke:#1ABC9C,color:#148F77,stroke-width:1px;
    classDef decision fill:#FDEDEC,stroke:#C0392B,color:#A93226,stroke-width:1px;
    classDef end fill:#F2F3F4,stroke:#99A3A4,color:#616A6B,stroke-width:1px;

    A["<i class='fa fa-user'></i> Pengguna Mengunggah Surat"]:::action --> B["<i class='fa fa-save'></i> Surat Disimpan<br>Status: 'Draft'"]:::process;
    B --> C{"<i class='fa fa-question-circle'></i> Perlu Tindak Lanjut?"}:::decision;
    C -- Ya --> D["<i class='fa fa-random'></i> Pilih Aksi di Halaman Detail"]:::action;
    D -- Opsi 1: Disposisi --> E["Status diubah menjadi 'Dikirim'"]:::process;
    D -- Opsi 2: Jadikan Tugas --> F["Status diubah menjadi 'Disetujui'"]:::process;
    C -- Tidak --> G["<i class='fa fa-archive'></i> Surat Diarsipkan"]:::end;
    E --> G;
    F --> G;
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
                            <p>Proses dimulai ketika seorang pengguna mencatat surat yang sudah ada ke dalam sistem. Setelah diunggah, surat akan berstatus <strong>'Draft'</strong>.</p>
                        </div>
                        <div>
                            <h4 class="font-semibold text-gray-800">2. Proses Tindak Lanjut</h4>
                            <p>Dari halaman detail surat, pengguna dapat memilih untuk menindaklanjuti surat tersebut:</p>
                            <ul class="list-disc list-inside ml-4 space-y-2">
                                <li><strong>Disposisi</strong>: Mengirim surat ke pengguna lain untuk diketahui atau ditindaklanjuti. Status surat akan berubah menjadi <strong>'Dikirim'</strong>.</li>
                                <li><strong>Menjadikan Tugas</strong>: Mengubah surat menjadi sebuah tugas yang dapat dikelola. Status surat akan berubah menjadi <strong>'Disetujui'</strong>.</li>
                                <li><strong>Tanpa Tindak Lanjut</strong>: Jika tidak ada aksi yang diambil, surat akan tetap tersimpan dan dapat diakses nanti.</li>
                            </ul>
                        </div>
                        <div>
                            <h4 class="font-semibold text-gray-800">3. Pengarsipan</h4>
                            <p>Semua surat yang telah selesai diproses (baik ditindaklanjuti maupun tidak) secara otomatis menjadi bagian dari arsip digital dan dapat dicari kembali melalui modul Arsip.</p>
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
