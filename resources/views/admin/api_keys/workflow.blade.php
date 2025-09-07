<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            <i class="fas fa-key mr-2"></i>
            {{ __('Alur Kerja Manajemen Integrasi (API)') }}
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
                    <h3 class="text-xl font-bold text-gray-800 mb-2">Dokumentasi Alur Kerja Integrasi API</h3>
                    <p class="text-gray-600">Halaman ini menjelaskan proses pengelolaan akses API untuk integrasi dengan sistem eksternal, termasuk pembuatan klien, token, dan penggunaan alat bantu.</p>
                </div>
            </x-card>

            <x-card>
                <div class="p-6">
                    <h3 class="text-xl font-bold text-gray-800 mb-4 border-b pb-2">Flowchart Alur Kerja</h3>
                    <p class="text-gray-600 mb-6">Flowchart ini merinci keseluruhan proses, dari pembuatan Klien API hingga penggunaan Token untuk mengakses data.</p>
                    <div class="p-4 bg-gray-50 rounded-lg text-center">
                        <pre class="mermaid">
graph TD
    classDef page fill:#EBF5FB,stroke:#3498DB,color:#2874A6,stroke-width:1px;
    classDef action fill:#FEF9E7,stroke:#F1C40F,color:#B7950B,stroke-width:1px;
    classDef process fill:#E8F8F5,stroke:#1ABC9C,color:#148F77,stroke-width:1px;
    classDef decision fill:#FDEDEC,stroke:#C0392B,color:#A93226,stroke-width:1px;
    classDef tool fill:#F4ECF7,stroke:#8E44AD,color:#6C3483,stroke-width:1px;

    subgraph "A. Konfigurasi oleh Admin"
        A1["<i class='fa fa-user-shield'></i> Admin"]:::action --> A2["<i class='fa fa-list-alt'></i> Halaman Manajemen API"]:::page;
        A2 --> A3["<i class='fa fa-plus-circle'></i> Buat Klien API Baru"]:::action;
        A3 --> A4["<i class='fa fa-save'></i> Simpan Klien"]:::process;
        A4 --> A2;

        A2 -- Untuk Klien yg Ada --> A5["<i class='fa fa-id-card'></i> Generate Token Baru"]:::action;
        A5 --> A6["<i class='fa fa-check-square'></i> Pilih Skop (Permissions)"]:::page;
        A6 --> A7["<i class='fa fa-key'></i> Generate & Tampilkan<br>Plain-Text Token (Hanya Sekali)"]:::process;
        A7 --> A8["<i class='fa fa-clipboard'></i> Admin Salin Token"]:::action;
        A8 --> A2;
    end

    subgraph "B. Penggunaan oleh Sistem Eksternal"
        B1["<i class='fa fa-robot'></i> Aplikasi Eksternal"]:::action;
        B2["<i class='fa fa-server'></i> Request ke Endpoint API<br>dgn 'Authorization: Bearer TOKEN'"]:::process;
        B3["<i class='fa fa-shield-alt'></i> Laravel Sanctum<br>Memvalidasi Token & Skop"]:::process;
        B4{Valid?}:::decision;
        B4 -- Ya --> B5["<i class='fa fa-database'></i> Akses Data Sesuai Skop"]:::process;
        B4 -- Tidak --> B6["<i class='fa fa-ban'></i> Respon 401/403 Unauthorized"]:::process;
        B5 --> B1;
        B6 --> B1;
    end

    subgraph "C. Alat Bantu (Tools)"
        T1["<i class='fa fa-book'></i> Halaman Dokumentasi"]:::tool;
        T2["<i class='fa fa-wrench'></i> Query Helper"]:::tool;
        A2 --> T1;
        A2 --> T2;
    end

    A8 --> B1;

                        </pre>
                    </div>
                </div>
            </x-card>

            <x-card>
                <div class="p-6">
                    <h3 class="text-xl font-bold text-gray-800 mb-4 border-b pb-2">Deskripsi Alur Kerja</h3>
                    <div class="prose max-w-none text-gray-700 space-y-4">
                        <div>
                            <h4 class="font-semibold text-gray-800">1. Konfigurasi Akses (Oleh Admin)</h4>
                            <p>Admin mengelola seluruh siklus hidup akses API melalui halaman Manajemen API.</p>
                            <ul class="list-disc list-inside ml-4 space-y-2">
                                <li><strong>Klien API</strong>: Langkah pertama adalah mendaftarkan sebuah "Klien", yang merepresentasikan aplikasi atau layanan yang akan mengakses data (contoh: "Aplikasi Mobile internal").</li>
                                <li><strong>Pembuatan Token</strong>: Untuk setiap klien, Admin dapat membuat satu atau lebih Token (API Key). Setiap token dapat memiliki cakupan (scope) yang berbeda. Misalnya, satu token hanya untuk membaca data proyek (`read:projects`), sementara token lain bisa membaca data pengguna (`read:users`).</li>
                                <li><strong>Keamanan Token</strong>: String token yang sebenarnya (plain-text) hanya akan **ditampilkan satu kali** saat pembuatan. Admin bertanggung jawab untuk menyalin dan menyimpan token ini di tempat yang aman untuk diberikan kepada pengembang aplikasi eksternal.</li>
                                <li><strong>Manajemen Siklus Hidup</strong>: Admin dapat menonaktifkan sementara seluruh Klien API atau mencabut (revoke) token tertentu secara individual tanpa mempengaruhi token lain.</li>
                            </ul>
                        </div>
                        <div>
                            <h4 class="font-semibold text-gray-800">2. Penggunaan API (Oleh Sistem Eksternal)</h4>
                            <p>Aplikasi eksternal yang telah menerima token dapat menggunakannya untuk mengambil data.</p>
                             <ul class="list-disc list-inside ml-4 space-y-2">
                                <li><strong>Request Terotentikasi</strong>: Aplikasi eksternal harus menyertakan token tersebut dalam *HTTP Header* setiap kali membuat permintaan ke API, dengan format `Authorization: Bearer [TOKEN_ANDA]`.</li>
                                <li><strong>Validasi oleh Sanctum</strong>: Sistem akan menggunakan Laravel Sanctum untuk memvalidasi token. Sanctum akan memeriksa apakah token itu ada, aktif, dan memiliki *scope* yang diperlukan untuk mengakses *endpoint* yang diminta.</li>
                                <li><strong>Respon</strong>: Jika valid, sistem akan memberikan data yang diminta. Jika tidak, sistem akan mengembalikan error `401 Unauthorized` atau `403 Forbidden`.</li>
                            </ul>
                        </div>
                         <div>
                            <h4 class="font-semibold text-gray-800">3. Alat Bantu Pengembang</h4>
                            <p>Untuk mempermudah proses integrasi, tersedia beberapa halaman bantuan:</p>
                             <ul class="list-disc list-inside ml-4 space-y-2">
                                <li><strong>Dokumentasi</strong>: Halaman yang menjelaskan *endpoint* API yang tersedia, parameter yang diterima, dan contoh respons.</li>
                                <li><strong>Query Helper</strong>: Alat interaktif yang membantu pengembang untuk membangun URL request API yang kompleks dengan filter dan parameter, tanpa perlu membaca kode secara manual.</li>
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
