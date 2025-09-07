<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            <i class="fas fa-tasks mr-2"></i>
            {{ __('Alur Kerja Modul Tugas Harian') }}
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

            <!-- Intro Card -->
            <x-card>
                <div class="p-6">
                    <h3 class="text-xl font-bold text-gray-800 mb-2">Dokumentasi Alur Kerja Tugas Harian</h3>
                    <p class="text-gray-600">Halaman ini berisi dokumentasi lengkap mengenai alur kerja Modul Tugas Harian (Non-Kegiatan), mulai dari pembuatan, pengelolaan, hingga pelaporan.</p>
                </div>
            </x-card>

            <!-- Flowchart Umum -->
            <x-card>
                <div class="p-6">
                    <h3 class="text-xl font-bold text-gray-800 mb-4 border-b pb-2">Flowchart Alur Kerja</h3>
                    <p class="text-gray-600 mb-6">Flowchart ini merinci keseluruhan alur kerja modul Tugas Harian.</p>
                    <div class="p-4 bg-gray-50 rounded-lg text-center">
                        <pre class="mermaid">
graph TD
    classDef page fill:#EBF5FB,stroke:#3498DB,color:#2874A6,stroke-width:1px;
    classDef action fill:#FEF9E7,stroke:#F1C40F,color:#B7950B,stroke-width:1px;
    classDef process fill:#E8F8F5,stroke:#1ABC9C,color:#148F77,stroke-width:1px;
    classDef decision fill:#FDEDEC,stroke:#C0392B,color:#A93226,stroke-width:1px;

    subgraph "A. Alur Utama"
        A1["<i class='fa fa-desktop'></i> Dashboard"]:::page -->|Klik Menu 'Tugas Harian'| A2["<i class='fa fa-list-alt'></i> Halaman Daftar Tugas Harian"]:::page;
        A2 -->|Klik 'Tambah Tugas'| B_Flow["<i class='fa fa-plus-circle'></i> Alur Pembuatan Tugas"];
        A2 -->|Klik 'Detail/Edit'| C_Flow["<i class='fa fa-edit'></i> Alur Edit Tugas"];
        A2 -->|Gunakan Filter| A2;
    end

    subgraph B_Flow [B. Alur Pembuatan Tugas Harian]
        B1[Mulai] --> B2{<i class='fa fa-shield-alt'></i> Cek Izin: 'create' Task}:::decision;
        B2 -- Diizinkan --> B3["<i class='fa fa-keyboard'></i> Form Tambah Tugas Harian"]:::page;
        B3 -- Isi Form & Submit --> B4{<i class='fa fa-check-double'></i> Validasi Input}:::decision;
        B4 -- Gagal --> B3;
        B4 -- Sukses --> B5["<i class='fa fa-save'></i> Simpan Task (project_id=null)"]:::process;
        B5 -- Notifikasi ke Penerima Tugas --> B6["<i class='fa fa-bell'></i> Kirim Notifikasi"]:::process;
        B6 --> A2;
    end

    subgraph C_Flow [C. Alur Edit & Detail Tugas]
        C1[Klik 'Detail/Edit'] --> C2{<i class='fa fa-shield-alt'></i> Cek Izin: 'update' Task}:::decision;
        C2 -- Diizinkan --> C3["<i class='fa fa-file-alt'></i> Halaman Edit Tugas (tasks.edit)"]:::page;
        C3 -- Ubah Data & Submit --> C4{<i class='fa fa-check-double'></i> Validasi Input}:::decision;
        C4 -- Gagal --> C3;
        C4 -- Sukses --> C5["<i class='fa fa-sync-alt'></i> Update Task"]:::process;
        C5 --> A2;
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
                            <h4 class="font-semibold text-gray-800">1. Halaman Daftar Tugas Harian (A)</h4>
                            <p>Ini adalah halaman utama untuk modul ini. Pengguna dapat melihat daftar semua tugas harian yang relevan dengan mereka (tugas mereka sendiri atau tugas bawahannya jika seorang manajer). Halaman ini dilengkapi dengan fitur pencarian dan filter berdasarkan status, prioritas, dan personel (untuk manajer).</p>
                        </div>
                        <div>
                            <h4 class="font-semibold text-gray-800">2. Alur Pembuatan Tugas (B)</h4>
                            <p>Pengguna dengan izin yang sesuai dapat membuat tugas baru. Prosesnya adalah sebagai berikut:</p>
                            <ul class="list-disc list-inside ml-4 space-y-2">
                                <li><strong>Formulir</strong>: Pengguna mengisi detail tugas seperti judul, deskripsi, deadline, dan prioritas. Manajer juga dapat memilih siapa yang akan ditugaskan.</li>
                                <li><strong>Validasi</strong>: Sistem memastikan semua data yang diperlukan telah diisi dengan benar.</li>
                                <li><strong>Penyimpanan</strong>: Tugas disimpan ke database dengan `project_id` diatur ke `null` untuk menandakannya sebagai tugas harian.</li>
                                <li><strong>Notifikasi</strong>: Sistem secara otomatis mengirimkan notifikasi kepada pengguna yang ditugaskan.</li>
                            </ul>
                        </div>
                        <div>
                            <h4 class="font-semibold text-gray-800">3. Alur Edit & Detail Tugas (C)</h4>
                            <p>Untuk melihat detail atau mengubah tugas yang ada, pengguna mengklik tombol 'Detail/Edit'. Ini akan mengarahkan mereka ke halaman edit tugas yang terpusat (ditangani oleh `TaskController`). Di halaman ini, pengguna dapat mengubah semua detail tugas, menambahkan lampiran, melihat sub-tugas, dan berkomentar.</p>
                        </div>
                         <div>
                            <h4 class="font-semibold text-gray-800">4. Laporan</h4>
                            <p>Di halaman daftar tugas, terdapat tombol 'Cetak Laporan' yang memungkinkan pengguna men-generate laporan tugas-tugas harian yang telah selesai dalam rentang tanggal tertentu.</p>
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
