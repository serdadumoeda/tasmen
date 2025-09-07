<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            <i class="fas fa-envelope-open-text mr-2"></i>
            {{ __('Alur Kerja Modul Surat Masuk') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">

            <!-- Intro Card -->
            <x-card>
                <div class="p-6">
                    <h3 class="text-xl font-bold text-gray-800 mb-2">Dokumentasi Alur Kerja Surat Masuk</h3>
                    <p class="text-gray-600">Halaman ini berisi dokumentasi lengkap mengenai alur kerja Modul Surat Masuk, mulai dari pengarsipan, disposisi, hingga tindak lanjut.</p>
                </div>
            </x-card>

            <!-- Flowchart Umum -->
            <x-card>
                <div class="p-6">
                    <h3 class="text-xl font-bold text-gray-800 mb-4 border-b pb-2">Flowchart Alur Kerja</h3>
                    <p class="text-gray-600 mb-6">Flowchart ini merinci keseluruhan alur kerja standar untuk modul Surat Masuk.</p>
                    <div class="p-4 bg-gray-50 rounded-lg text-center">
                        <pre class="mermaid">
graph TD
    classDef page fill:#EBF5FB,stroke:#3498DB,color:#2874A6,stroke-width:1px;
    classDef action fill:#FEF9E7,stroke:#F1C40F,color:#B7950B,stroke-width:1px;
    classDef process fill:#E8F8F5,stroke:#1ABC9C,color:#148F77,stroke-width:1px;
    classDef decision fill:#FDEDEC,stroke:#C0392B,color:#A93226,stroke-width:1px;

    subgraph "A. Pengarsipan Surat"
        A1["<i class='fa fa-user'></i> Staf/Admin"]:::action -->|Klik 'Arsipkan Surat Baru'| A2["<i class='fa fa-keyboard'></i> Form Arsip Surat"]:::page;
        A2 -- Isi data & upload file --> A3{<i class='fa fa-check-double'></i> Validasi}:::decision;
        A3 -- Gagal --> A2;
        A3 -- Sukses --> A4["<i class='fa fa-save'></i> Simpan Surat & Lampiran"]:::process;
        A4 --> A5["<i class='fa fa-paper-plane'></i> Disposisi Otomatis<br>ke Kepala Unit"]:::process;
        A5 --> A6["<i class='fa fa-list-alt'></i> Surat Tampil di<br>Daftar Surat Masuk"]:::page;
    end

    subgraph "B. Proses Disposisi"
        B1["<i class='fa fa-user-tie'></i> Kepala Unit"]:::action -->|Buka Notifikasi/Detail Surat| B2["<i class='fa fa-file-alt'></i> Halaman Detail Surat"]:::page;
        B2 --> B3["<i class='fa fa-users'></i> Form Buat Disposisi"]:::action;
        B3 -- Pilih Penerima &<br>Isi Instruksi --> B4{<i class='fa fa-check-double'></i> Validasi}:::decision;
        B4 -- Gagal --> B3;
        B4 -- Sukses --> B5["<i class='fa fa-save'></i> Simpan Disposisi Baru"]:::process;
        B5 --> B6["<i class='fa fa-bell'></i> Notifikasi ke Penerima Disposisi"]:::process;
        B6 --> B7["<i class='fa fa-user'></i> Staf/Penerima Disposisi"];
        B7 --> B2;
    end

    subgraph "C. Tindak Lanjut Surat"
        C1["<i class='fa fa-user'></i> Penerima Disposisi"]:::action -->|Buka Detail Surat| C2["<i class='fa fa-file-alt'></i> Halaman Detail Surat"]:::page;
        C2 --> C3{<i class='fa fa-question-circle'></i> Perlu Tindak Lanjut?}:::decision;
        C3 -- Ya --> C4["<i class='fa fa-tasks'></i> Buat Tugas dari Surat"]:::action;
        C4 --> C5["<i class='fa fa-arrow-right'></i> Redirect ke Form Edit Tugas"]:::page;
        C3 -- Tidak --> C6["<i class='fa fa-archive'></i> Arsip (Selesai)"];
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
                            <h4 class="font-semibold text-gray-800">1. Pengarsipan Surat (A)</h4>
                            <p>Proses dimulai ketika seorang staf atau admin menerima surat fisik atau digital. Mereka kemudian mengarsipkan surat tersebut ke dalam sistem.</p>
                            <ul class="list-disc list-inside ml-4 space-y-2">
                                <li><strong>Input Data</strong>: Pengguna memasukkan metadata surat (nomor, perihal, tanggal) dan mengunggah file pindaian surat.</li>
                                <li><strong>Disposisi Otomatis</strong>: Setelah surat berhasil disimpan, sistem secara otomatis membuat disposisi pertama kepada Kepala Unit dari pengguna yang mengarsipkan. Ini memastikan surat tidak berhenti dan langsung masuk ke alur persetujuan/peninjauan.</li>
                            </ul>
                        </div>
                        <div>
                            <h4 class="font-semibold text-gray-800">2. Proses Disposisi (B)</h4>
                            <p>Setelah disposisi pertama dibuat, proses disposisi berjenjang dapat terjadi. Seorang pimpinan yang menerima disposisi dapat meneruskannya (mendisposisikan ulang) ke bawahannya.</p>
                            <ul class="list-disc list-inside ml-4 space-y-2">
                                <li><strong>Halaman Detail Surat</strong>: Ini adalah pusat untuk melihat histori disposisi dan membuat disposisi baru. Pengguna dapat melihat seluruh jejak perjalanan surat.</li>
                                <li><strong>Membuat Disposisi Baru</strong>: Pengguna memilih satu atau lebih penerima, memberikan instruksi, dan mengirim. Sistem akan mencatat disposisi baru dan mengirim notifikasi ke penerima berikutnya.</li>
                            </ul>
                        </div>
                        <div>
                            <h4 class="font-semibold text-gray-800">3. Tindak Lanjut Surat (C)</h4>
                            <p>Pada akhirnya, surat akan sampai pada staf atau pejabat yang bertanggung jawab untuk menindaklanjutinya.</p>
                             <ul class="list-disc list-inside ml-4 space-y-2">
                                <li><strong>Membuat Tugas</strong>: Jika surat tersebut memerlukan sebuah aksi atau pekerjaan, pengguna dapat langsung membuat tugas baru dari halaman detail surat. Sistem akan secara otomatis menautkan tugas tersebut dengan surat asalnya, memastikan keterlacakan (traceability).</li>
                                <li><strong>Selesai</strong>: Jika surat hanya bersifat informatif dan tidak memerlukan aksi lebih lanjut, proses dianggap selesai dan surat tersimpan sebagai arsip.</li>
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
