<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            <i class="fas fa-calendar-check mr-2"></i>
            {{ __('Alur Kerja Modul Manajemen Cuti') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">

            <x-card>
                <div class="p-6">
                    <h3 class="text-xl font-bold text-gray-800 mb-2">Dokumentasi Alur Kerja Cuti</h3>
                    <p class="text-gray-600">Halaman ini berisi dokumentasi lengkap mengenai alur kerja Modul Manajemen Cuti, mulai dari pengajuan oleh pegawai, proses persetujuan berjenjang, hingga penerbitan SK Cuti otomatis. Alur ini memastikan proses yang transparan dan akuntabel.</p>
                </div>
            </x-card>

            <x-card>
                <div class="p-6">
                    <h3 class="text-xl font-bold text-gray-800 mb-4 border-b pb-2">Flowchart Alur Kerja</h3>
                    <p class="text-gray-600 mb-6">Flowchart ini merinci keseluruhan alur kerja standar untuk modul Manajemen Cuti.</p>
                    <div class="p-4 bg-gray-50 rounded-lg text-center">
                        <pre class="mermaid">
graph TD
    classDef page fill:#EBF5FB,stroke:#3498DB;
    classDef action fill:#FEF9E7,stroke:#F1C40F;
    classDef process fill:#E8F8F5,stroke:#1ABC9C;
    classDef decision fill:#FDEDEC,stroke:#C0392B;
    classDef notif fill:#F4ECF7,stroke:#8E44AD;

    subgraph sg1 [A. Alur Pengajuan]
        A1["<i class='fa fa-user'></i> Pegawai"]:::action;
        A2["<i class='fa fa-keyboard'></i> Form Pengajuan Cuti"]:::page;
        A3{<i class='fa fa-check-double'></i> Validasi & Cek Saldo}:::decision;
        A4["<i class='fa fa-save'></i> Simpan Permintaan Cuti"]:::process;
        A5["<i class='fa fa-bell'></i> Notifikasi ke Atasan"]:::notif;
    end

    subgraph sg2 [B. Alur Persetujuan Berjenjang]
        B1["<i class='fa fa-user-tie'></i> Atasan"]:::action;
        B2["<i class='fa fa-file-alt'></i> Halaman Detail Cuti"]:::page;
        B3["<i class='fa fa-cogs'></i> LeaveApprovalService"]:::process;
        B4{<i class='fa fa-question-circle'></i> Ada Jenjang Berikutnya?}:::decision;
        B5["Ubah Status & Teruskan"]:::process;
        B6["<i class='fa fa-bell'></i> Notifikasi ke Atasan Berikutnya"]:::notif;
        B7["Ubah Status: APPROVED"]:::process;
        B8["<i class='fa fa-gavel'></i> Form Alasan Penolakan"]:::page;
        B9["Ubah Status: REJECTED"]:::process;
        B10["<i class='fa fa-bell'></i> Notifikasi ke Pegawai"]:::notif;
    end

    subgraph sg3 [C. Alur Penerbitan SK Otomatis]
        C1["<i class='fa fa-cogs'></i> SuratCutiGenerator"]:::process;
        C2["<i class='fa fa-file-word'></i> Buat Dokumen SK Cuti"]:::process;
        C3["<i class='fa fa-save'></i> Simpan Surat & Tautkan"]:::process;
        C4["<i class='fa fa-check-circle'></i> Selesai"];
    end

    A1 --> A2;
    A2 -- Submit --> A3;
    A3 -- Gagal --> A2;
    A3 -- Sukses --> A4;
    A4 --> A5;
    A5 --> B1;

    B1 --> B2;
    B2 -- Setujui --> B3;
    B3 --> B4;

    B4 -- Ya --> B5;
    B5 --> B6;
    B6 --> B1;

    B4 -- Tidak (Final) --> B7;
    B7 --> C1;

    B2 -- Tolak --> B8;
    B8 -- Submit --> B9;
    B9 --> B10;

    C1 --> C2;
    C2 --> C3;
    C3 --> C4;
</pre>
                    </div>
                </div>
            </x-card>

            <x-card>
                <div class="p-6">
                    <h3 class="text-xl font-bold text-gray-800 mb-4 border-b pb-2">Penjelasan Detail Alur Kerja</h3>
                    <div class="prose max-w-none text-gray-700 space-y-4">
                        <div>
                            <h4 class="font-semibold text-gray-800">1. Alur Pengajuan (A)</h4>
                            <p>Proses dimulai ketika seorang pegawai mengajukan cuti melalui form yang tersedia. Sistem akan memvalidasi sisa saldo cuti sebelum menyimpan permintaan dan mengirim notifikasi ke atasan.</p>
                        </div>
                        <div>
                            <h4 class="font-semibold text-gray-800">2. Alur Persetujuan Berjenjang (B)</h4>
                            <p>Ini adalah inti dari modul Cuti. Proses persetujuan tidak statis, melainkan dinamis berdasarkan workflow yang telah diatur untuk unit kerja pegawai. `LeaveApprovalService` akan secara otomatis meneruskan permintaan ke level atasan berikutnya hingga persetujuan final tercapai atau permintaan ditolak.</p>
                        </div>
                        <div>
                            <h4 class="font-semibold text-gray-800">3. Penerbitan SK Otomatis (C)</h4>
                            <p>Setelah persetujuan final, `SuratCutiGenerator` akan dipanggil untuk membuat dokumen SK Cuti dari template. Dokumen ini kemudian disimpan dan ditautkan ke permintaan cuti yang bersangkutan untuk memastikan semua data terhubung.</p>
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
