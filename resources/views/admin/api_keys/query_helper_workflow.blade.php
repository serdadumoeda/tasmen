<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            <i class="fas fa-magic mr-2"></i>
            {{ __('Alur Kerja API Query Helper') }}
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
                    <h3 class="text-xl font-bold text-gray-800 mb-2">Dokumentasi API Query Helper</h3>
                    <p class="text-gray-600">Halaman ini menjelaskan cara kerja dan tujuan dari alat bantu "API Query Helper", yang dirancang untuk mempermudah pengembang dalam membangun dan menguji URL permintaan API.</p>
                </div>
            </x-card>

            <x-card>
                <div class="p-6">
                    <h3 class="text-xl font-bold text-gray-800 mb-4 border-b pb-2">Flowchart Alur Kerja</h3>
                    <div class="p-4 bg-gray-50 rounded-lg text-center">
                        <pre class="mermaid">
graph TD
    classDef page fill:#EBF5FB,stroke:#3498DB;
    classDef action fill:#FEF9E7,stroke:#F1C40F;
    classDef process fill:#E8F8F5,stroke:#1ABC9C;
    classDef output fill:#F4ECF7,stroke:#8E44AD;

    A(Pengembang):::action --> B(Buka Halaman Query Helper):::page;
    B --> C(1. Pilih Sumber Daya API<br>e.g., 'Proyek', 'Pengguna'):::action;
    C --> D(2. Pilih Filter yang Tersedia<br>e.g., 'status', 'unit_id'):::action;
    D --> E(3. Isi Nilai Filter<br>e.g., 'completed', '5'):::action;
    E --> F(4. Klik 'Generate URL'):::action;
    F --> G(JavaScript Membangun String URL):::process;
    G --> H(Tampilkan URL Lengkap<br>dengan Query Parameters):::output;
    H --> I(Pengembang Salin URL<br>untuk digunakan di aplikasi lain):::action;
                        </pre>
                    </div>
                </div>
            </x-card>

            <x-card>
                <div class="p-6">
                    <h3 class="text-xl font-bold text-gray-800 mb-4 border-b pb-2">Deskripsi Alur Kerja</h3>
                    <div class="prose max-w-none text-gray-700 space-y-4">
                        <div>
                            <h4 class="font-semibold text-gray-800">1. Tujuan Alat Bantu</h4>
                            <p>API Query Helper adalah alat bantu visual (*visual tool*) yang ditujukan untuk pengembang atau administrator teknis. Tujuannya adalah untuk mempermudah pembuatan URL API yang kompleks tanpa harus menghafal semua nama filter dan parameter yang tersedia.</p>
                        </div>
                        <div>
                            <h4 class="font-semibold text-gray-800">2. Cara Penggunaan</h4>
                            <p>Alur penggunaannya sangat sederhana dan interaktif:</p>
                            <ul class="list-disc list-inside ml-4 space-y-2">
                                <li><strong>Pilih Sumber Daya</strong>: Pengembang memilih *endpoint* utama yang ingin mereka tuju, seperti `/api/v1/projects` atau `/api/v1/users`.</li>
                                <li><strong>Pilih Filter</strong>: Berdasarkan sumber daya yang dipilih, *dropdown* kedua akan secara dinamis menampilkan filter-filter yang relevan untuk sumber daya tersebut (misalnya, filter `status` untuk Proyek).</li>
                                <li><strong>Isi Nilai</strong>: Pengembang memasukkan nilai yang ingin mereka gunakan untuk filter.</li>
                                <li><strong>Generate URL</strong>: Dengan menekan tombol "Generate", JavaScript di halaman tersebut akan secara otomatis membangun URL lengkap dengan *query string* yang benar, contoh: `https://app.url/api/v1/projects?filter[status]=completed`.</li>
                            </ul>
                        </div>
                        <div>
                            <h4 class="font-semibold text-gray-800">3. Manfaat</h4>
                            <p>Alat ini secara signifikan mempercepat proses pengembangan dan pengujian saat berintegrasi dengan API aplikasi ini, mengurangi kemungkinan kesalahan pengetikan (*typo*) pada nama parameter, dan berfungsi sebagai dokumentasi interaktif untuk filter-filter yang tersedia.</p>
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
                theme: 'base'
            });
        </script>
    @endpush
</x-app-layout>
