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

    %% --- A. Alur Utama ---
    A1["Halaman Daftar<br>Surat Keluar"]:::page;

    %% --- B. Alur Pembuatan ---
    subgraph B_Flow [Alur Pembuatan Surat Keluar]
        B1[Mulai] --> B2["Pilih Metode"]:::page;
        B2 -->|'Dari Template'| B3["Pilih Template"]:::page;
        B3 --> B4["Isi Konten"]:::page;
        B4 -- Submit --> B_Save;

        B2 -->|'Unggah Manual'| B5["Unggah PDF"]:::page;
        B5 -- Submit --> B_Save;

        B_Save["Simpan Draf &<br>Generate Nomor"]:::process;
    end

    %% --- C. Alur Persetujuan ---
    subgraph C_Flow [Alur Detail & Persetujuan]
        C1["Halaman Detail (Draft)"]:::page --> C2["Aksi: Setujui"]:::action;
        C2 -- Pilih Pejabat & TTD --> C3{Validasi}:::decision;
        C3 -- Gagal --> C1;
        C3 -- Sukses --> C4["Panggil TTE Service"]:::process;
        C4 -- Gagal TTD --> C5["Tampilkan Error"]:::io;
        C4 -- Sukses TTD --> C6["Simpan PDF Final"]:::process;
    end

    %% --- D. Alur Tindak Lanjut ---
    subgraph D_Flow [Alur Tindak Lanjut (Opsional)]
        D1["Surat Disetujui"]:::page --> D2["Aksi: Buat SK Penugasan"]:::action;
        D2 --> D3["Redirect ke Form SK"]:::page;
    end

    %% --- Menghubungkan Alur ---
    A1 -->|Klik 'Buat Surat'| B1;
    A1 -->|Klik 'Detail'| C1;
    B_Save --> C1;
    C6 --> D1;
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
                            <h4 class="font-semibold text-gray-800">1. Alur Pembuatan Surat</h4>
                            <p>Proses pembuatan surat keluar memberikan fleksibilitas kepada pengguna:</p>
                            <ul class="list-disc list-inside ml-4 space-y-2">
                                <li><strong>Pilihan Metode</strong>: Pengguna bisa memilih untuk membuat surat dari awal menggunakan template yang sudah ada, atau mengunggah file PDF yang sudah jadi.</li>
                                <li><strong>Penyimpanan Draf</strong>: Apapun metodenya, surat akan disimpan sebagai **draf** dan sistem akan secara otomatis memberikan **nomor surat**.</li>
                            </ul>
                        </div>
                        <div>
                            <h4 class="font-semibold text-gray-800">2. Alur Persetujuan & Tanda Tangan Digital</h4>
                            <p>Setelah draf disimpan, surat siap untuk proses persetujuan oleh pejabat yang berwenang.</p>
                            <ul class="list-disc list-inside ml-4 space-y-2">
                                <li><strong>Aksi Persetujuan</strong>: Pejabat memilih tombol 'Setujui' di halaman detail.</li>
                                <li><strong>Proses TTE</strong>: Jika Tanda Tangan Elektronik (TTE) dipilih, sistem akan memanggil service khusus untuk menambahkan QR Code dan TTE pada dokumen.</li>
                                <li><strong>Hasil</strong>: Jika berhasil, PDF final yang sudah ditandatangani akan disimpan, dan status surat berubah menjadi 'Disetujui'.</li>
                            </ul>
                        </div>
                        <div>
                            <h4 class="font-semibold text-gray-800">3. Alur Tindak Lanjut</h4>
                            <p>Setelah sebuah surat keluar disetujui, surat tersebut dapat menjadi dasar untuk aksi selanjutnya, seperti membuat SK Penugasan baru.</p>
                        </div>
                    </div>
                </div>
            </x-card>

        </div>
    </div>

    @push('scripts')
    <script type="module">
        import mermaid from 'https://cdn.jsdelivr.net/npm/mermaid@10/dist/mermaid.esm.min.mjs';

        // Set konfigurasi di awal
        mermaid.initialize({
            startOnLoad: false, // <-- UBAH INI JADI FALSE
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

        // Panggil mermaid.run() secara eksplisit setelah DOM siap
        document.addEventListener('DOMContentLoaded', function() {
            // Memberi sedikit delay jika ada rendering komponen lain
            setTimeout(() => {
                mermaid.run({
                    querySelector: '.mermaid' // Pastikan hanya merender elemen dengan class mermaid
                });
            }, 100); // Delay 100ms
        });
    </script>
    @endpush
</x-app-layout>
