<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            <i class="fas fa-archive mr-2"></i>
            {{ __('Alur Kerja Modul Arsip Digital') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">

            <!-- Intro Card -->
            <x-card>
                <div class="p-6">
                    <h3 class="text-xl font-bold text-gray-800 mb-2">Dokumentasi Alur Kerja Arsip Digital</h3>
                    <p class="text-gray-600">Halaman ini berisi dokumentasi lengkap mengenai alur kerja Modul Arsip Digital, yang berfungsi sebagai pusat pencarian dan pengorganisasian semua surat yang telah diarsipkan.</p>
                </div>
            </x-card>

            <!-- Flowchart Umum -->
            <x-card>
                <div class="p-6">
                    <h3 class="text-xl font-bold text-gray-800 mb-4 border-b pb-2">Flowchart Alur Kerja</h3>
                    <p class="text-gray-600 mb-6">Flowchart ini merinci keseluruhan alur kerja standar untuk modul Arsip Digital.</p>
                    <div class="p-4 bg-gray-50 rounded-lg text-center">
                        <pre class="mermaid">
graph TD
    classDef page fill:#EBF5FB,stroke:#3498DB,color:#2874A6,stroke-width:1px;
    classDef action fill:#FEF9E7,stroke:#F1C40F,color:#B7950B,stroke-width:1px;
    classDef process fill:#E8F8F5,stroke:#1ABC9C,color:#148F77,stroke-width:1px;
    classDef decision fill:#FDEDEC,stroke:#C0392B,color:#A93226,stroke-width:1px;

    subgraph "A. Pencarian & Filter"
        A1["<i class='fa fa-search'></i> Halaman Arsip Digital"]:::page --> A2["Isi Form Filter<br>(Kata Kunci, Tgl, Jenis, Klasifikasi)"]:::action;
        A2 -- Klik 'Cari' --> A3["<i class='fa fa-cogs'></i> Controller: ArsipController@index"]:::process;
        A3 --> A4["Tampilkan Hasil Pencarian"]:::page;
    end

    subgraph "B. Manajemen Berkas (Folder)"
        B1["Sidebar Berkas"] --> B2["<i class='fa fa-folder-plus'></i> Isi Form Buat Berkas Baru"]:::action;
        B2 -- Klik 'Buat' --> B3{<i class='fa fa-check-double'></i> Validasi}:::decision;
        B3 -- Gagal --> B2;
        B3 -- Sukses --> B4["<i class='fa fa-save'></i> Controller: ArsipController@storeBerkas"]:::process;
        B4 --> B5["<i class='fa fa-sync-alt'></i> Refresh Daftar Berkas"]:::page;
    end

    subgraph "C. Mengelompokkan Surat ke Berkas"
        C1["Daftar Hasil Pencarian Surat"]:::page --> C2["<i class='fa fa-check-square'></i> Pilih satu atau<br>lebih surat (checkbox)"]:::action;
        C2 --> C3["<i class='fa fa-folder'></i> Pilih Berkas Tujuan<br>dari dropdown"]:::action;
        C3 -- Klik 'Masukkan ke Berkas' --> C4{<i class='fa fa-check-double'></i> Validasi}:::decision;
        C4 -- Gagal --> C1;
        C4 -- Sukses --> C5["<i class='fa fa-save'></i> Controller:<br>ArsipController@addSuratToBerkas"]:::process;
        C5 --> C6["<i class='fa fa-info-circle'></i> Tampilkan notifikasi<br>sukses"]:::page;
    end

    %% --- Hubungan antar alur ---
    A4 --> C1
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
                            <h4 class="font-semibold text-gray-800">1. Pencarian & Filter (A)</h4>
                            <p>Halaman utama modul ini adalah mesin pencari yang kuat untuk semua surat (masuk dan keluar) yang sudah selesai diproses. Pengguna dapat dengan mudah menemukan surat berdasarkan berbagai kriteria.</p>
                        </div>
                        <div>
                            <h4 class="font-semibold text-gray-800">2. Manajemen Berkas (B)</h4>
                            <p>Pengguna dapat membuat "berkas" atau folder virtual pribadi untuk mengorganisir surat-surat penting. Proses ini dilakukan di sidebar halaman.</p>
                            <ul class="list-disc list-inside ml-4 space-y-2">
                                <li><strong>Buat Berkas</strong>: Pengguna mengisi nama dan deskripsi untuk berkas baru, lalu menyimpannya. Daftar berkas akan langsung diperbarui.</li>
                            </ul>
                        </div>
                        <div>
                            <h4 class="font-semibold text-gray-800">3. Mengelompokkan Surat (C)</h4>
                            <p>Ini adalah fungsi inti dari modul arsip. Setelah menemukan surat-surat yang relevan melalui pencarian, pengguna dapat mengelompokkannya ke dalam berkas.</p>
                             <ul class="list-disc list-inside ml-4 space-y-2">
                                <li><strong>Pilih Surat</strong>: Pengguna menandai satu atau lebih surat menggunakan checkbox di samping setiap baris.</li>
                                <li><strong>Pilih Berkas Tujuan</strong>: Pengguna memilih salah satu berkas yang sudah mereka buat dari menu dropdown di bagian bawah tabel.</li>
                                 <li><strong>Simpan</strong>: Dengan mengklik tombol "Masukkan ke Berkas", sistem akan membuat relasi antara surat-surat yang dipilih dengan berkas tujuan.</li>
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
