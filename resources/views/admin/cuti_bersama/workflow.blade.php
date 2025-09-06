<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            <i class="fas fa-gifts mr-2"></i>
            {{ __('Alur Kerja Manajemen Cuti Bersama') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">

            <x-card>
                <div class="p-6">
                    <h3 class="text-xl font-bold text-gray-800 mb-2">Dokumentasi Alur Cuti Bersama</h3>
                    <p class="text-gray-600">Halaman ini menjelaskan proses pengelolaan tanggal cuti bersama nasional, yang akan mempengaruhi perhitungan jatah cuti seluruh pegawai.</p>
                </div>
            </x-card>

            <x-card>
                <div class="p-6">
                    <h3 class="text-xl font-bold text-gray-800 mb-4 border-b pb-2">Flowchart Alur Kerja</h3>
                    <p class="text-gray-600 mb-6">Flowchart ini merinci proses standar CRUD (Create, Read, Update, Delete) untuk pengelolaan cuti bersama.</p>
                    <div class="p-4 bg-gray-50 rounded-lg text-center">
                        <pre class="mermaid">
graph TD
    classDef page fill:#EBF5FB,stroke:#3498DB,color:#2874A6,stroke-width:1px;
    classDef action fill:#FEF9E7,stroke:#F1C40F,color:#B7950B,stroke-width:1px;
    classDef process fill:#E8F8F5,stroke:#1ABC9C,color:#148F77,stroke-width:1px;
    classDef decision fill:#FDEDEC,stroke:#C0392B,color:#A93226,stroke-width:1px;
    classDef effect fill:#F5EEF8,stroke:#9B59B6,color:#7D3C98,stroke-width:1px;

    A["<i class='fa fa-user-shield'></i> Admin"]:::action --> B["<i class='fa fa-list-alt'></i> Halaman Daftar<br>Cuti Bersama"]:::page;
    B --> C{Pilih Aksi}:::decision;

    subgraph "CRUD Actions"
        C -- Tambah --> D["<i class='fa fa-plus-circle'></i> Form Tambah Data"]:::page;
        C -- Edit --> E["<i class='fa fa-edit'></i> Form Edit Data"]:::page;
        C -- Hapus --> F{Konfirmasi Hapus}:::decision;
    end

    subgraph "Proses Simpan"
        D -- Isi Form --> G{Validasi Data}:::decision;
        E -- Ubah Data --> G;
        G -- Valid --> H["<i class='fa fa-save'></i> Simpan/Update ke DB"]:::process;
        G -- Tidak Valid --> I["<i class='fa fa-exclamation-triangle'></i> Tampilkan Error"]:::process;
        H --> B;
        I --> D;
    end

    subgraph "Proses Hapus"
        F -- Ya --> J["<i class='fa fa-trash'></i> Hapus dari DB"]:::process;
        F -- Tidak --> B;
        J --> B;
    end

    subgraph "Dampak Sistem"
      H --> K["<i class='fa fa-calculator'></i> Service Perhitungan Cuti<br>akan menggunakan data ini"]:::effect;
      J --> K;
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
                            <h4 class="font-semibold text-gray-800">1. Pengelolaan Data</h4>
                            <p>Admin yang memiliki hak akses dapat mengelola daftar tanggal yang ditetapkan sebagai cuti bersama. Prosesnya sangat sederhana:</p>
                            <ul class="list-disc list-inside ml-4 space-y-2">
                                <li><strong>Tambah</strong>: Admin menambahkan tanggal baru beserta nama atau deskripsi (contoh: "Cuti Bersama Idul Fitri 1445 H").</li>
                                <li><strong>Edit</strong>: Mengubah detail tanggal atau deskripsi jika ada kesalahan.</li>
                                <li><strong>Hapus</strong>: Menghapus tanggal dari daftar cuti bersama.</li>
                            </ul>
                        </div>
                        <div>
                            <h4 class="font-semibold text-gray-800">2. Dampak pada Sistem</h4>
                            <p>Meskipun pengelolaannya sederhana, data ini memiliki dampak penting pada modul lain, terutama Manajemen Cuti:</p>
                             <ul class="list-disc list-inside ml-4 space-y-2">
                                <li><strong>Perhitungan Durasi Cuti</strong>: Ketika seorang pegawai mengajukan cuti, `LeaveDurationService` akan secara otomatis memeriksa daftar tanggal cuti bersama.</li>
                                <li><strong>Tidak Mengurangi Jatah</strong>: Jika rentang tanggal cuti yang diajukan oleh pegawai mencakup tanggal cuti bersama, maka hari tersebut **tidak akan dihitung** sebagai pengurang jatah cuti tahunan pegawai.</li>
                                <li><strong>Contoh</strong>: Pegawai mengajukan cuti dari 8 Agustus hingga 12 Agustus. Jika 9 Agustus adalah cuti bersama, maka sistem hanya akan menghitung 4 hari kerja yang digunakan, bukan 5.</li>
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
