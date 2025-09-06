<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            <i class="fas fa-paper-plane mr-2"></i>
            {{ __('Alur Kerja Modul Surat Keluar') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">

            <x-card>
                <div class="p-6">
                    <h3 class="text-xl font-bold text-gray-800 mb-2">Dokumentasi Alur Kerja Surat Keluar</h3>
                    <p class="text-gray-600">Halaman ini berisi dokumentasi lengkap mengenai alur kerja Modul Surat Keluar, mulai dari pemilihan metode pembuatan, pengisian draf, proses persetujuan, penandatanganan digital, hingga tindak lanjut.</p>
                </div>
            </x-card>

            <x-card>
                <div class="p-6">
                    <h3 class="text-xl font-bold text-gray-800 mb-4 border-b pb-2">Flowchart Alur Kerja</h3>
                    <p class="text-gray-600 mb-6">Flowchart ini merinci keseluruhan alur kerja standar untuk modul Surat Keluar.</p>
                    <div class="p-4 bg-gray-50 rounded-lg text-center">
                        <pre class="mermaid">
graph TD
    classDef page fill:#EBF5FB,stroke:#3498DB,color:#2874A6,stroke-width:1px;
    classDef action fill:#FEF9E7,stroke:#F1C40F,color:#B7950B,stroke-width:1px;
    classDef process fill:#E8F8F5,stroke:#1ABC9C,color:#148F77,stroke-width:1px;
    classDef decision fill:#FDEDEC,stroke:#C0392B,color:#A93226,stroke-width:1px;
    classDef io fill:#F4ECF7,stroke:#8E44AD,color:#6C3483,stroke-width:1px;

    subgraph "A. Pembuatan Surat"
        A1["<i class='fa fa-user'></i> Staf/Pengguna"]:::action --> A2["<i class='fa fa-th-list'></i> Halaman Pilih Metode"]:::page;
        A2 -->|Dari Template| A3["<i class='fa fa-file-word'></i> Pilih Template & Isi Konten"]:::page;
        A2 -->|Unggah Manual| A4["<i class='fa fa-upload'></i> Unggah Berkas PDF"]:::page;
        A3 -- Submit --> A5["<i class='fa fa-cogs'></i> Simpan Draf &<br>Generate Nomor Surat"]:::process;
        A4 -- Submit --> A5;
    end

    subgraph "B. Persetujuan & TTE"
        B1["<i class='fa fa-file-alt'></i> Halaman Detail Surat (Draf)"]:::page;
        B2["<i class='fa fa-user-tie'></i> Pejabat Berwenang"]:::action --> B3["<i class='fa fa-mouse-pointer'></i> Klik 'Setujui Surat'"]:::action;
        B3 --> B4["<i class='fa fa-signature'></i> Pilih Opsi Tanda Tangan<br>(Dengan/Tanpa TTE)"]:::page;
        B4 --> B5{Dengan TTE?}:::decision;
        B5 -- Ya --> B6["<i class='fa fa-qrcode'></i> Panggil TTE Service<br>untuk generate QR Code"]:::process;
        B5 -- Tidak --> B8["<i class='fa fa-check'></i> Status: 'Disetujui'"]:::process;
        B6 -- Gagal --> B7["<i class='fa fa-exclamation-triangle'></i> Tampilkan Error TTE"]:::io;
        B6 -- Sukses --> B8;
        B8 --> B9["<i class='fa fa-file-pdf'></i> Simpan PDF Final<br>ke Penyimpanan Aman"]:::process;
    end

    subgraph "C. Tindak Lanjut"
        C1["<i class='fa fa-file-powerpoint'></i> Surat Final (Disetujui)"]:::page --> C2["<i class='fa fa-download'></i> Unduh/Arsipkan"]:::action;
        C1 --> C3["<i class='fa fa-file-signature'></i> Buat SK Penugasan<br>berdasarkan Surat"]:::action;
        C3 --> C4["<i class='fa fa-arrow-right'></i> Redirect ke Form SK<br>dengan data terisi"]:::page;
    end

    %% --- Menghubungkan Alur ---
    A5 --> B1;
    B1 --> B2;
    B9 --> C1;

</pre>
                    </div>
                </div>
            </x-card>

            <x-card>
                <div class="p-6">
                    <h3 class="text-xl font-bold text-gray-800 mb-4 border-b pb-2">Penjelasan Detail Alur Kerja</h3>
                    <div class="prose max-w-none text-gray-700 space-y-4">
                        <div>
                            <h4 class="font-semibold text-gray-800">1. Alur Pembuatan Surat (A)</h4>
                            <p>Proses pembuatan surat keluar memberikan fleksibilitas kepada pengguna untuk mengakomodasi berbagai kebutuhan:</p>
                            <ul class="list-disc list-inside ml-4 space-y-2">
                                <li><strong>Pilihan Metode</strong>: Pengguna dapat memilih untuk membuat surat dari awal menggunakan template yang sudah ada di sistem, atau mengunggah file PDF yang sudah jadi jika surat dibuat di luar aplikasi.</li>
                                <li><strong>Nomor Surat Otomatis</strong>: Apapun metodenya, saat draf pertama kali disimpan, sistem akan secara otomatis memanggil `NomorSuratService` untuk menghasilkan nomor surat yang unik berdasarkan klasifikasi dan urutan yang berlaku.</li>
                                <li><strong>Penyimpanan Draf</strong>: Surat akan disimpan sebagai **draf**, memungkinkan pengguna untuk meninjau dan mengeditnya sebelum diajukan untuk persetujuan.</li>
                            </ul>
                        </div>
                        <div>
                            <h4 class="font-semibold text-gray-800">2. Alur Persetujuan & Tanda Tangan Digital (B)</h4>
                            <p>Setelah draf final, surat siap untuk proses persetujuan oleh pejabat yang berwenang.</p>
                            <ul class="list-disc list-inside ml-4 space-y-2">
                                <li><strong>Aksi Persetujuan</strong>: Pejabat yang ditunjuk membuka halaman detail surat dan memilih tombol 'Setujui Surat'.</li>
                                <li><strong>Opsi Tanda Tangan</strong>: Pejabat diberikan pilihan apakah surat ini memerlukan Tanda Tangan Elektronik (TTE) atau tidak.</li>
                                <li><strong>Proses TTE</strong>: Jika TTE dipilih, sistem akan memanggil service TTE (misalnya, koneksi ke BSrE) untuk menyematkan QR Code pada dokumen PDF. Proses ini mengubah dokumen draf menjadi dokumen final yang sah secara digital.</li>
                                <li><strong>Penyimpanan Final</strong>: PDF final yang sudah ditandatangani (atau yang disetujui tanpa TTE) akan disimpan di penyimpanan yang aman, dan status surat diubah menjadi 'Disetujui'.</li>
                            </ul>
                        </div>
                        <div>
                            <h4 class="font-semibold text-gray-800">3. Alur Tindak Lanjut (C)</h4>
                            <p>Setelah sebuah surat keluar disetujui, surat tersebut dapat menjadi dasar untuk aksi selanjutnya, menciptakan alur kerja yang terintegrasi:</p>
                            <ul class="list-disc list-inside ml-4 space-y-2">
                                <li><strong>Distribusi & Pengarsipan</strong>: Surat final dapat diunduh untuk didistribusikan atau diintegrasikan dengan modul Arsip Digital.</li>
                                <li><strong>Dasar SK Penugasan</strong>: Fitur yang paling kuat adalah kemampuan untuk langsung membuat SK Penugasan dari surat yang telah disetujui. Sistem akan me-redirect pengguna ke form pembuatan SK baru dengan beberapa data (seperti perihal) yang sudah terisi otomatis dari surat tersebut.</li>
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
