<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            <i class="fas fa-calendar-check mr-2"></i>
            {{ __('Alur Kerja Modul Manajemen Cuti') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">

            <!-- Intro Card -->
            <x-card>
                <div class="p-6">
                    <h3 class="text-xl font-bold text-gray-800 mb-2">Dokumentasi Alur Kerja Cuti</h3>
                    <p class="text-gray-600">Halaman ini berisi dokumentasi lengkap mengenai alur kerja Modul Manajemen Cuti, mulai dari pengajuan oleh pegawai, proses persetujuan berjenjang, hingga penerbitan SK Cuti otomatis.</p>
                </div>
            </x-card>

            <!-- Flowchart Umum -->
            <x-card>
                <div class="p-6">
                    <h3 class="text-xl font-bold text-gray-800 mb-4 border-b pb-2">Flowchart Alur Kerja</h3>
                    <p class="text-gray-600 mb-6">Flowchart ini merinci keseluruhan alur kerja standar untuk modul Manajemen Cuti.</p>
                    <div class="p-4 bg-gray-50 rounded-lg text-center">
                        <pre class="mermaid">
graph TD
    classDef page fill:#EBF5FB,stroke:#3498DB,color:#2874A6,stroke-width:1px;
    classDef action fill:#FEF9E7,stroke:#F1C40F,color:#B7950B,stroke-width:1px;
    classDef process fill:#E8F8F5,stroke:#1ABC9C,color:#148F77,stroke-width:1px;
    classDef decision fill:#FDEDEC,stroke:#C0392B,color:#A93226,stroke-width:1px;
    classDef io fill:#F4ECF7,stroke:#8E44AD,color:#6C3483,stroke-width:1px;

    %% --- Definisi Elemen ---
    subgraph A [Alur Pengajuan]
        A1["<i class='fa fa-user'></i> Pegawai"]:::action;
        A2["<i class='fa fa-keyboard'></i> Form Pengajuan Cuti"]:::page;
        A3{<i class='fa fa-check-double'></i> Validasi & Cek Saldo}:::decision;
        A4["<i class='fa fa-save'></i> Simpan Permintaan Cuti<br>(Status: PENDING)"]:::process;
        A5["<i class='fa fa-bell'></i> Notifikasi ke Atasan"]:::process;
    end

    subgraph B [Alur Persetujuan Berjenjang]
        B1["<i class='fa fa-user-tie'></i> Atasan"]:::action;
        B2["<i class='fa fa-file-alt'></i> Halaman Detail Cuti"]:::page;
        B3["<i class='fa fa-cogs'></i> LeaveApprovalService"]:::process;
        B4{<i class='fa fa-question-circle'></i> Ada Jenjang Berikutnya?}:::decision;
        B5["Ubah Status: 'APPROVED_BY_SUPERVISOR'<br>Update Penyetuju Berikutnya"]:::process;
        B6["<i class='fa fa-bell'></i> Notifikasi ke Atasan Berikutnya"]:::process;
        B7["Ubah Status: 'APPROVED'"]:::process;
        B8["<i class='fa fa-gavel'></i> Form Alasan Penolakan"]:::page;
        B9["Ubah Status: 'REJECTED'"]:::process;
        B10["<i class='fa fa-bell'></i> Notifikasi ke Pegawai"]:::process;
    end

    subgraph C [Alur Penerbitan SK Otomatis]
        C1["<i class='fa fa-cogs'></i> SuratCutiGenerator"]:::process;
        C2["<i class='fa fa-file-word'></i> Buat Dokumen SK Cuti"]:::process;
        C3["<i class='fa fa-save'></i> Simpan Surat & Tautkan"]:::process;
        C4["<i class='fa fa-check-circle'></i> Selesai"];
    end

    %% --- Menghubungkan Alur ---
    A1 -->|Klik 'Ajukan Cuti'| A2;
    A2 -- Submit --> A3;
    A3 -- Gagal --> A2;
    A3 -- Sukses --> A4;
    A4 --> A5;
    A5 --> B1;

    B1 -->|Buka Notifikasi| B2;
    B2 -->|Klik 'Setujui'| B3;
    B3 --> B4;

    B4 -- Ya --> B5;
    B5 --> B6;
    B6 --> B1;

    B4 -- Tidak (Final) --> B7;
    B7 --> C1;

    B2 -->|Klik 'Tolak'| B8;
    B8 -- Submit --> B9;
    B9 --> B10;

    C1 --> C2;
    C2 --> C3;
    C3 --> C4;
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
                            <h4 class="font-semibold text-gray-800">1. Alur Pengajuan (A)</h4>
                            <p>Proses dimulai ketika seorang pegawai mengajukan cuti.</p>
                            <ul class="list-disc list-inside ml-4 space-y-2">
                                <li><strong>Pengisian Form</strong>: Pegawai mengisi jenis cuti, rentang tanggal, alasan, dan lampiran jika diperlukan.</li>
                                <li><strong>Validasi Saldo</strong>: Jika jenis cuti adalah cuti tahunan, sistem akan otomatis memeriksa sisa saldo cuti yang tersedia. Jika tidak cukup, pengajuan akan ditolak.</li>
                                <li><strong>Notifikasi Awal</strong>: Setelah berhasil diajukan, sistem secara otomatis mengirimkan notifikasi ke atasan langsung dari pegawai tersebut untuk memulai proses persetujuan.</li>
                            </ul>
                        </div>
                        <div>
                            <h4 class="font-semibold text-gray-800">2. Alur Persetujuan Berjenjang (B)</h4>
                            <p>Ini adalah inti dari modul Cuti. Proses persetujuan tidak statis, melainkan dinamis berdasarkan workflow yang telah diatur untuk unit kerja pegawai.</p>
                            <ul class="list-disc list-inside ml-4 space-y-2">
                                <li><strong>Persetujuan Bertingkat</strong>: Seorang atasan yang menyetujui permintaan akan memicu `LeaveApprovalService`. Service ini akan memeriksa apakah ada tingkat persetujuan selanjutnya berdasarkan `ApprovalWorkflow` yang berlaku.</li>
                                <li><strong>Penerusan (Forward)</strong>: Jika ada tingkat selanjutnya, status permintaan diubah menjadi 'Disetujui oleh Atasan' dan notifikasi dikirim ke atasan berikutnya dalam hierarki yang memiliki peran (role) yang sesuai.</li>
                                <li><strong>Persetujuan Final</strong>: Jika tidak ada lagi tingkat persetujuan, permintaan dianggap disetujui sepenuhnya.</li>
                                <li><strong>Penolakan</strong>: Atasan di tingkat manapun dapat menolak permintaan dengan memberikan alasan. Proses akan berhenti dan notifikasi penolakan dikirim ke pegawai.</li>
                            </ul>
                        </div>
                        <div>
                            <h4 class="font-semibold text-gray-800">3. Penerbitan SK Otomatis (C)</h4>
                            <p>Setelah persetujuan final, sistem secara otomatis melakukan proses administrasi akhir.</p>
                             <ul class="list-disc list-inside ml-4 space-y-2">
                                <li><strong>Generate Surat</strong>: `SuratCutiGenerator` service akan dipanggil untuk membuat dokumen SK Cuti dari template yang sudah ada.</li>
                                <li><strong>Integrasi</strong>: Dokumen surat yang baru dibuat akan otomatis tersimpan di modul Persuratan dan ditautkan ke permintaan cuti yang bersangkutan, memastikan semua data terhubung.</li>
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
