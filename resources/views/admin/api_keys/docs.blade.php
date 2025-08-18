<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Panduan Penggunaan API') }}
            </h2>
            <a href="{{ route('admin.api_keys.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-900 focus:outline-none focus:border-gray-900 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150">
                <i class="fas fa-arrow-left mr-2"></i>
                Kembali ke Manajemen Kunci API
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 sm:p-8 text-gray-900 space-y-8">

                    {{-- Introduction --}}
                    <div>
                        <h3 class="text-2xl font-semibold border-b pb-2 mb-4">Pengantar</h3>
                        <p class="text-gray-700 leading-relaxed">
                            Dokumen ini adalah panduan praktis bagi tim teknis untuk mengintegrasikan sistem mereka dengan API Tasmen. API ini memungkinkan sistem eksternal untuk mengakses data secara terprogram dan aman.
                        </p>
                    </div>

                    {{-- Authentication --}}
                    <div>
                        <h3 class="text-2xl font-semibold border-b pb-2 mb-4">1. Autentikasi</h3>
                        <p class="text-gray-700 leading-relaxed mb-4">
                            Semua permintaan (request) ke API harus menyertakan sebuah **API Key** yang valid. Kunci ini harus dikirimkan dalam *header* HTTP `Authorization` dengan skema `Bearer`.
                        </p>
                        <p class="text-gray-700 leading-relaxed">
                            Format Header:
                        </p>
                        <div class="mt-2 p-3 bg-gray-100 border rounded-md text-sm">
                            <code class="font-mono">Authorization: Bearer &lt;API_KEY_ANDA_DISINI&gt;</code>
                        </div>
                        <p class="mt-4 text-gray-700">
                            <strong>Penting:</strong> Jaga kerahasiaan API Key Anda. Jangan pernah menampilkannya di kode sisi klien (frontend) atau menyimpannya di lokasi yang tidak aman.
                        </p>
                    </div>

                    {{-- Example Request --}}
                    <div>
                        <h3 class="text-2xl font-semibold border-b pb-2 mb-4">2. Contoh Permintaan (Request)</h3>
                        <p class="text-gray-700 leading-relaxed mb-4">
                            Cara termudah untuk menguji koneksi Anda adalah dengan membuat permintaan `GET` ke endpoint status. Endpoint ini akan memverifikasi apakah API Key Anda valid dan aktif.
                        </p>
                        <p class="text-gray-700 leading-relaxed font-semibold">
                            Endpoint Status:
                        </p>
                        <div class="mt-2 p-3 bg-gray-100 border rounded-md text-sm">
                            <code class="font-mono">{{ url('/api/v1/status') }}</code>
                        </div>

                        <p class="mt-4 text-gray-700 leading-relaxed font-semibold">
                            Contoh menggunakan cURL:
                        </p>
                        <div class="mt-2 p-4 bg-gray-800 text-white rounded-md text-sm">
                            <pre><code class="language-bash">curl -X GET "{{ url('/api/v1/status') }}" \
-H "Authorization: Bearer 1|aBcDeFgHiJkLmNoPqRs..." \
-H "Accept: application/json"</code></pre>
                        </div>
                        <p class="mt-4 text-gray-600 text-sm">
                            Ganti `1|aBcDeFgHiJkLmNoPqRs...` dengan API Key yang telah Anda terima.
                        </p>
                    </div>

                    {{-- Response Format --}}
                    <div>
                        <h3 class="text-2xl font-semibold border-b pb-2 mb-4">3. Format Respons</h3>
                        <p class="text-gray-700 leading-relaxed mb-4">
                            Semua respons dari API akan dibungkus dalam format JSON yang konsisten (disebut "envelope") untuk mempermudah pemrosesan.
                        </p>

                        <p class="text-gray-700 leading-relaxed font-semibold">
                            Contoh Respons Sukses:
                        </p>
                        <div class="mt-2 p-4 bg-gray-800 text-white rounded-md text-sm">
                            <pre><code class="language-json">{
    "success": true,
    "message": "API service is running and accessible.",
    "data": {
        "service_status": "online",
        "authenticated_client": "Nama Client Anda",
        "timestamp": "2025-08-17T21:30:00.000000Z"
    }
}</code></pre>
                        </div>
                        <p class="mt-4 text-gray-700 leading-relaxed">
                            Jika `success` bernilai `true`, artinya permintaan Anda berhasil diproses. Data yang Anda minta akan berada di dalam properti `data`.
                        </p>

                        <p class="mt-6 text-gray-700 leading-relaxed font-semibold">
                            Contoh Respons Gagal (Key tidak valid):
                        </p>
                        <div class="mt-2 p-4 bg-gray-800 text-white rounded-md text-sm">
                            <pre><code class="language-json">{
    "success": false,
    "message": "Invalid API Key.",
    "data": null
}</code></pre>
                        </div>
                        <p class="mt-4 text-gray-700 leading-relaxed">
                            Jika `success` bernilai `false`, periksa `message` untuk mengetahui penyebab kegagalan.
                        </p>
                    </div>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
