<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            <i class="fas fa-paper-plane mr-2"></i>
            {{ __('Alur Kerja Modul Surat Keluar') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">

            <!-- Intro Card -->
            <x-card>
                <div class="p-6">
                    <h3 class="text-xl font-bold text-gray-800 mb-2">Dokumentasi Alur Kerja Surat Keluar</h3>
                    <p class="text-gray-600">Halaman ini berisi dokumentasi lengkap mengenai alur kerja Modul Surat Keluar, mulai dari pemilihan metode pembuatan, pengisian draf, hingga proses persetujuan dan penandatanganan digital.</p>
                </div>
            </x-card>

            <!-- Flowchart Umum -->
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

    subgraph "A. Alur Utama"
        A1["<i class='fa fa-list-alt'></i> Halaman Daftar Surat Keluar"]:::page -->|Klik 'Buat Surat Baru'| B_Flow["<i class='fa fa-plus-circle'></i> Alur Pembuatan Surat"];
        A1 -->|Klik 'Detail'| C_Flow["<i class='fa fa-file-alt'></i> Alur Detail & Persetujuan"];
    end

    subgraph B_Flow [B. Alur Pembuatan Surat Keluar]
        B1[Mulai] --> B2["<i class='fa fa-th-list'></i> Pilih Metode Pembuatan"]:::page;
        B2 -->|Pilih 'Dari Template'| B3["<i class='fa fa-file-word'></i> Pilih Template Surat"]:::page;
        B3 --> B4["<i class='fa fa-keyboard'></i> Isi Konten & Placeholder"]:::page;
        B4 -- Submit --> B_Save;

        B2 -->|Pilih 'Unggah Manual'| B5["<i class='fa fa-upload'></i> Unggah File PDF"]:::page;
        B5 -- Submit --> B_Save;

        B_Save{"<i class='fa fa-cogs'></i> Simpan sebagai Draf<br>+ Generate Nomor Surat"}:::process --> C_Flow;
    end

    subgraph C_Flow [C. Alur Detail & Persetujuan]
        C1["<i class='fa fa-file-alt'></i> Halaman Detail Surat (Draft)"]:::page --> C2["<i class='fa fa-user-check'></i> Aksi: Setujui Surat"]:::action;
        C2 -- Pilih Pejabat &<br>Tentukan TTD Digital --> C3{<i class='fa fa-check-double'></i> Validasi}:::decision;
        C3 -- Gagal --> C1;
        C3 -- Sukses --> C4["<i class='fa fa-signature'></i> Panggil TTE Service<br>untuk Tanda Tangan Digital"]:::process;
        C4 -- Gagal TTD --> C5["<i class='fa fa-exclamation-triangle'></i> Tampilkan Error"]:::io;
        C4 -- Sukses TTD --> C6["<i class='fa fa-file-pdf'></i> Simpan PDF Final &<br>Ubah Status jadi 'Disetujui'"]:::process;
        C6 --> D_Flow["<i class='fa fa-cogs'></i> Alur Tindak Lanjut"];
    end

    subgraph D_Flow [D. Alur Tindak Lanjut (Opsional)]
        D1["<i class='fa fa-file-powerpoint'></i> Surat Sudah Disetujui"]:::page --> D2["<i class='fa fa-file-signature'></i> Aksi: Buat SK Penugasan"]:::action;
        D2 --> D3["<i class='fa fa-arrow-right'></i> Redirect ke Form<br>Buat SK Penugasan"]:::page;
    end
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
                            <h4 class="font-semibold text-gray-800">1. Alur Pembuatan Surat (B)</h4>
                            <p>Proses pembuatan surat keluar memberikan fleksibilitas kepada pengguna:</p>
                            <ul class="list-disc list-inside ml-4 space-y-2">
                                <li><strong>Pilihan Metode</strong>: Pengguna bisa memilih untuk membuat surat dari awal menggunakan template yang sudah ada, atau mengunggah file PDF yang sudah jadi.</li>
                                <li><strong>Pembuatan dari Template</strong>: Pengguna memilih template, lalu mengisi konten surat dan placeholder yang dinamis (misalnya, nama penerima).</li>
                                <li><strong>Unggah Manual</strong>: Pengguna langsung mengunggah file PDF final.</li>
                                <li><strong>Penyimpanan Draf</strong>: Apapun metodenya, surat akan disimpan sebagai **draf** dan sistem akan secara otomatis memberikan **nomor surat** menggunakan `NomorSuratService`.</li>
                            </ul>
                        </div>
                        <div>
                            <h4 class="font-semibold text-gray-800">2. Alur Persetujuan & Tanda Tangan Digital (C)</h4>
                            <p>Setelah draf disimpan, surat siap untuk proses persetujuan oleh pejabat yang berwenang.</p>
                            <ul class="list-disc list-inside ml-4 space-y-2">
                                <li><strong>Halaman Detail</strong>: Di halaman ini, pejabat dapat me-review draf surat.</li>
                                <li><strong>Aksi Persetujuan</strong>: Pejabat memilih tombol 'Setujui'. Mereka akan diminta untuk memilih pejabat penandatangan dan menentukan apakah akan menggunakan Tanda Tangan Elektronik (TTE) atau tidak.</li>
                                <li><strong>Proses TTE</strong>: Jika TTE dipilih, sistem akan memanggil `TteManager` service. Service ini akan menambahkan QR Code (untuk verifikasi) dan gambar tanda tangan ke dokumen, lalu mengirimkannya ke provider TTE untuk ditandatangani secara digital.</li>
                                <li><strong>Hasil</strong>: Jika berhasil, PDF final yang sudah ditandatangani akan disimpan, dan status surat berubah menjadi 'Disetujui'. Jika gagal, surat tetap berstatus 'Draft' dan pesan error ditampilkan.</li>
                            </ul>
                        </div>
                        <div>
                            <h4 class="font-semibold text-gray-800">3. Alur Tindak Lanjut (D)</h4>
                            <p>Setelah sebuah surat keluar disetujui, surat tersebut dapat menjadi dasar untuk aksi selanjutnya.</p>
                             <ul class="list-disc list-inside ml-4 space-y-2">
                                <li><strong>Buat SK Penugasan</strong>: Terdapat fitur untuk langsung membuat SK Penugasan baru dari surat keluar yang sudah disetujui. Ini akan mengarahkan pengguna ke form pembuatan SK Penugasan dengan beberapa data yang sudah terisi dari surat (misalnya, perihal surat menjadi judul SK).</li>
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
