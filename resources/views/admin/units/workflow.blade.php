<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            <i class="fas fa-sitemap mr-2"></i>
            {{ __('Alur Kerja Manajemen Organisasi') }}
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

            <x-card>
                <div class="p-6">
                    <h3 class="text-xl font-bold text-gray-800 mb-2">Dokumentasi Alur Kerja Terpadu</h3>
                    <p class="text-gray-600">Halaman ini menjelaskan alur kerja terpadu untuk mengelola seluruh struktur organisasi, yang mencakup Unit Kerja, Jabatan, dan penempatan Pengguna di dalamnya. Halaman utama **Manajemen Unit** adalah pusat dari semua aktivitas ini.</p>
                </div>
            </x-card>

            <x-card>
                <div class="p-6">
                    <h3 class="text-xl font-bold text-gray-800 mb-4 border-b pb-2">Flowchart Alur Kerja Organisasi</h3>
                    <p class="text-gray-600 mb-6">Flowchart ini merinci keseluruhan proses utama dalam pengelolaan struktur organisasi dari halaman utama Manajemen Unit.</p>
                    <div class="p-4 bg-gray-50 rounded-lg text-center">
                        <pre class="mermaid">
graph TD
    classDef page fill:#EBF5FB,stroke:#3498DB,color:#2874A6;
    classDef action fill:#FEF9E7,stroke:#F1C40F,color:#B7950B;
    classDef process fill:#E8F8F5,stroke:#1ABC9C,color:#148F77;
    classDef decision fill:#FDEDEC,stroke:#C0392B,color:#A93226;
    classDef data fill:#F5EEF8,stroke:#9B59B6,color:#7D3C98;

    subgraph "Manajemen Unit (Tampilan Hierarki)"
        Start[Mulai] --> A(Buka Manajemen Unit):::page;
        A --> B{Pilih Aksi}:::decision;
        B -- "Tambah Unit Baru" --> C(Form Tambah Unit):::page;
        B -- "Edit Unit" --> D(Masuk ke Halaman Edit Unit):::action;
        B -- "Tambah Pengguna ke Unit" --> E(Buka Modal Tambah Pengguna):::action;
        C --> F(Simpan Unit):::process --> A;
        E --> G(Pilih Pengguna & Simpan):::process --> A;
    end

    subgraph "Halaman Edit Unit"
        D --> H(Daftar Jabatan di Unit Ini):::page;
        H --> I{Pilih Aksi Jabatan}:::decision;
        I -- "Tambah Jabatan" --> J(Isi Form Jabatan):::page --> K(Simpan Jabatan):::process --> D;
        I -- "Edit Jabatan" --> L(Form Edit Jabatan):::page --> M(Update Jabatan):::process --> D;
    end

    subgraph "Manajemen Pengguna (Tampilan Daftar)"
        X(Menu Manajemen Pengguna):::page --> Y(Daftar Pengguna):::page;
        Y -- "Struktur Organisasi" --> A;
    end

    Start --> X;
                        </pre>
                    </div>
                </div>
            </x-card>

            <x-card>
                <div class="p-6">
                    <h3 class="text-xl font-bold text-gray-800 mb-4 border-b pb-2">Deskripsi Alur Kerja</h3>
                    <div class="prose max-w-none text-gray-700 space-y-4">
                        <div>
                            <h4 class="font-semibold text-gray-800">1. Pusat Manajemen Organisasi</h4>
                            <p>Menu <strong>Manajemen Unit</strong> kini menjadi pusat untuk melihat dan mengelola seluruh struktur organisasi. Halaman ini menampilkan hierarki unit kerja dalam bentuk pohon interaktif yang dapat dicari. Dari sini, Admin dapat melakukan aksi-aksi utama:</p>
                            <ul class="list-disc list-inside ml-4 space-y-2">
                                <li><strong>Tambah Unit Baru</strong>: Membuat unit kerja baru di tingkat paling atas.</li>
                                <li><strong>Edit Unit</strong>: Membuka halaman detail sebuah unit untuk mengelola informasi spesifik unit tersebut serta jabatan di dalamnya.</li>
                                <li><strong>Tambah Pengguna ke Unit</strong>: Membuka jendela modal untuk menempatkan pengguna yang belum memiliki jabatan langsung ke dalam sebuah unit.</li>
                            </ul>
                        </div>
                        <div>
                            <h4 class="font-semibold text-gray-800">2. Mengelola Jabatan di Dalam Unit</h4>
                            <p>Setelah masuk ke halaman <strong>Edit Unit</strong>, Admin dapat mengelola daftar jabatan yang ada di dalam unit tersebut.</p>
                             <ul class="list-disc list-inside ml-4 space-y-2">
                                <li><strong>Tambah Jabatan</strong>: Membuat posisi atau jabatan baru yang terikat pada unit tersebut.</li>
                                <li><strong>Edit Jabatan</strong>: Mengubah nama jabatan dan memberikan izin khusus seperti "Dapat Mengelola Pengguna".</li>
                            </ul>
                        </div>
                        <div>
                            <h4 class="font-semibold text-gray-800">3. Hubungan dengan Manajemen Pengguna</h4>
                            <p>Menu <strong>Manajemen Pengguna</strong> tetap menjadi tempat utama untuk mengelola data individual pengguna (seperti NIK, NIP, profil, dll.) dalam format tabel. Untuk melihat posisi pengguna dalam struktur, tombol <strong>"Struktur Organisasi"</strong> akan mengarahkan Admin kembali ke halaman utama Manajemen Unit.</p>
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
