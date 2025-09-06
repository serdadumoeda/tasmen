<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            <i class="fas fa-cogs mr-2"></i>
            {{ __('Alur Kerja Pengaturan Aplikasi') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">

            <x-card>
                <div class="p-6">
                    <h3 class="text-xl font-bold text-gray-800 mb-2">Dokumentasi Alur Kerja Pengaturan</h3>
                    <p class="text-gray-600">Halaman ini menjelaskan proses pengelolaan pengaturan umum dan pengaturan rumus yang digunakan di seluruh aplikasi.</p>
                </div>
            </x-card>

            <x-card>
                <div class="p-6">
                    <h3 class="text-xl font-bold text-gray-800 mb-4 border-b pb-2">Flowchart Alur Kerja</h3>
                    <p class="text-gray-600 mb-6">Flowchart ini merinci proses pembaruan pengaturan umum dan rumus.</p>
                    <div class="p-4 bg-gray-50 rounded-lg text-center">
                        <pre class="mermaid">
graph TD
    classDef page fill:#EBF5FB,stroke:#3498DB,color:#2874A6,stroke-width:1px;
    classDef action fill:#FEF9E7,stroke:#F1C40F,color:#B7950B,stroke-width:1px;
    classDef process fill:#E8F8F5,stroke:#1ABC9C,color:#148F77,stroke-width:1px;
    classDef decision fill:#FDEDEC,stroke:#C0392B,color:#A93226,stroke-width:1px;

    A["<i class='fa fa-user-shield'></i> Admin"]:::action --> B{Pilih Menu Pengaturan};
    B --> C["<i class='fa fa-sliders-h'></i> Pengaturan Umum"]:::page;
    B --> D["<i class='fa fa-calculator'></i> Pengaturan Rumus"]:::page;

    subgraph "Proses Update"
        C -- Ubah Data --> E["<i class='fa fa-keyboard'></i> Form Pengaturan"];
        D -- Ubah Rumus --> E;
        E -- Submit --> F{<i class='fa fa-check-double'></i> Validasi Data & Rumus}:::decision;
        F -- Gagal --> G["<i class='fa fa-exclamation-triangle'></i> Tampilkan Error"]:::process;
        F -- Sukses --> H["<i class='fa fa-save'></i> Simpan Key-Value ke DB"]:::process;
        G --> E;
        H --> I["<i class='fa fa-check-circle'></i> Tampilkan Pesan Sukses"]:::process;
        I --> B;
    end

    subgraph "Fitur Tambahan"
        D --> J["<i class='fa fa-play-circle'></i> Simulasi Rumus"]:::action;
        J --> K["API Call ke 'simulate'"]:::process;
        K --> L["Tampilkan Hasil<br>secara Real-time"]:::process;
        L --> D;
    end

                        </pre>
                    </div>
                </div>
            </x-card>

            <x-card>
                <div class="p-6">
                    <h3 class="text-xl font-bold text-gray-800 mb-4 border-b pb-2">Deskripsi Alur Kerja</h3>
                    <div class="prose max-w-none text-gray-700 space-y-4">
                        <div>
                            <h4 class="font-semibold text-gray-800">1. Akses Menu Pengaturan</h4>
                            <p>Admin dapat mengakses dua halaman pengaturan utama dari menu navigasi:</p>
                            <ul class="list-disc list-inside ml-4 space-y-2">
                                <li><strong>Pengaturan Umum</strong>: Untuk mengubah parameter dasar aplikasi seperti Nama Aplikasi, deskripsi, dan mengunggah logo perusahaan.</li>
                                <li><strong>Pengaturan Rumus</strong>: Halaman khusus untuk mendefinisikan rumus perhitungan kinerja, seperti Indeks Kinerja Individu (IKI) dan Nilai Kinerja Final (NKF).</li>
                            </ul>
                        </div>
                        <div>
                            <h4 class="font-semibold text-gray-800">2. Proses Pembaruan</h4>
                            <p>Meskipun halamannya berbeda, proses pembaruan mengikuti alur yang sama:</p>
                             <ul class="list-disc list-inside ml-4 space-y-2">
                                <li><strong>Input Data</strong>: Admin mengubah nilai pada form yang tersedia.</li>
                                <li><strong>Validasi</strong>: Saat disimpan, sistem melakukan validasi. Untuk Pengaturan Rumus, validasi ini sangat ketat, memeriksa sintaks dan variabel yang diizinkan untuk mencegah error saat kalkulasi.</li>
                                <li><strong>Penyimpanan</strong>: Jika valid, setiap pengaturan disimpan sebagai pasangan `key` dan `value` di dalam database. Untuk logo, file akan diunggah ke server dan path-nya yang akan disimpan.</li>
                            </ul>
                        </div>
                        <div>
                            <h4 class="font-semibold text-gray-800">3. Simulasi Rumus</h4>
                            <p>Pada halaman Pengaturan Rumus, terdapat fitur simulasi. Fitur ini memungkinkan Admin untuk menguji rumus secara *real-time* dengan memasukkan nilai-nilai variabel dummy. Sistem akan mengirim request ke API internal untuk mengevaluasi rumus dan menampilkan hasilnya tanpa harus menyimpannya terlebih dahulu. Ini sangat berguna untuk memastikan logika rumus sudah benar sebelum diterapkan secara global.</p>
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
