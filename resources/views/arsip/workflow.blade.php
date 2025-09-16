<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            <i class="fas fa-archive mr-2"></i>
            {{ __('Alur Kerja Arsip Digital') }}
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
                    <h3 class="text-xl font-bold text-gray-800 mb-2">Dokumentasi Alur Arsip Digital</h3>
                    <p class="text-gray-600">Halaman ini menjelaskan proses penggunaan modul Arsip Digital, yang memungkinkan pengguna untuk mencari dan mengelompokkan surat-surat penting ke dalam berkas virtual pribadi.</p>
                </div>
            </x-card>

            <x-card>
                <div class="p-6">
                    <h3 class="text-xl font-bold text-gray-800 mb-4 border-b pb-2">Flowchart Alur Kerja</h3>
                    <p class="text-gray-600 mb-6">Flowchart ini merinci bagaimana pengguna berinteraksi dengan arsip dan mengelola berkas virtual mereka sesuai dengan logika terbaru.</p>
                    <div class="p-4 bg-gray-50 rounded-lg text-center">
                        <pre class="mermaid">
graph TD
    classDef page fill:#EBF5FB,stroke:#3498DB,color:#2874A6,stroke-width:1px;
    classDef action fill:#FEF9E7,stroke:#F1C40F,color:#B7950B,stroke-width:1px;
    classDef process fill:#E8F8F5,stroke:#1ABC9C,color:#148F77,stroke-width:1px;
    classDef modal fill:#F4ECF7,stroke:#A569BD,color:#633974,stroke-width:1px;
    classDef data fill:#FADBD8,stroke:#E74C3C,color:#B03A2E,stroke-width:1px;

    subgraph "A. Halaman Utama Arsip"
        A1["<i class='fa fa-user'></i> Pengguna"]:::action --> A2["<i class='fa fa-archive'></i> Halaman Arsip Digital"]:::page;
        A2 -- Menampilkan --> A3["<i class='fa fa-envelope-open'></i> Daftar Semua Surat"]:::process;
        A2 -- Menampilkan Juga --> A4["<i class='fa fa-folder'></i> Daftar Berkas Virtual"]:::process;
    end

    subgraph "B. Aksi pada Surat"
        A3 --> B1["<i class='fa fa-exchange-alt'></i> Klik 'Pindahkan' pada Surat"]:::action;
        B1 --> B2["<i class='fa fa-window-restore'></i> Modal Pindahkan Surat"]:::modal;
        B2 -- Pilih Berkas & Simpan --> B3["Sistem Memperbarui<br>surat.berkas_id &<br>surat.status = 'diarsipkan'"]:::process;
    end

    subgraph "C. Aksi pada Berkas Virtual"
        A4 --> C1["<i class='fa fa-folder-plus'></i> Isi Form 'Buat Berkas Baru'"]:::action;
        C1 --> C2["Sistem Membuat<br>Record Berkas Baru"]:::process;

        A4 --> C3["<i class='fa fa-ellipsis-v'></i> Klik Menu '...' pada Berkas"]:::action;
        C3 --> C4["<i class='fa fa-edit'></i> Pilih 'Edit'"]:::action;
        C4 --> C5["<i class='fa fa-window-restore'></i> Modal Edit Berkas"]:::modal;
        C5 -- Simpan Perubahan --> C6["Sistem Memperbarui<br>Nama/Deskripsi Berkas"]:::process;

        C3 --> C7["<i class='fa fa-trash'></i> Pilih 'Hapus'"]:::action;
        C7 -- Konfirmasi --> C8["Sistem Menghapus Berkas<br>& Mengatur surat.berkas_id = NULL<br>untuk semua surat terkait"]:::process;
    end

    subgraph "D. Melihat Isi Berkas"
        A4 --> D1["<i class='fa fa-folder-open'></i> Klik Nama Berkas"]:::action;
        D1 --> D2["<i class='fa fa-file-alt'></i> Halaman Detail Berkas"]:::page;
        D2 -- Menampilkan --> D3["Daftar Surat<br><i>Hanya yang ada di dalam berkas</i>"]:::process;
        D3 --> B1;
    end

    subgraph "E. Aksi dari Halaman Detail Surat"
        E1["<i class='fa fa-file-alt'></i> Halaman Detail Surat"]:::page --> E2["<i class='fa fa-archive'></i> Klik 'Arsipkan' atau 'Pindahkan'"]:::action;
        E2 --> B2;
    end

    subgraph "Sumber Data"
        S1["Database Surat"]:::data;
        S2["Database Berkas"]:::data;
        S1 --> A3;
        S2 --> A4;
        B3 --> S1;
        C2 --> S2;
        C6 --> S2;
        C8 --> S1 & S2;
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
                            <h4 class="font-semibold text-gray-800">1. Halaman Terpusat</h4>
                            <p>Halaman utama Arsip Digital adalah pusat kendali Anda. Halaman ini menampilkan dua komponen utama: daftar semua surat yang relevan dan daftar berkas virtual yang telah Anda buat.</p>
                        </div>
                        <div>
                            <h4 class="font-semibold text-gray-800">2. Mengelola Berkas Virtual</h4>
                            <p>Anda memiliki kontrol penuh atas berkas virtual Anda, yang berfungsi seperti folder untuk mengorganisir surat.</p>
                             <ul class="list-disc list-inside ml-4 space-y-2">
                                <li><strong>Buat Berkas</strong>: Gunakan formulir di sidebar untuk membuat berkas baru dengan nama dan deskripsi yang jelas.</li>
                                <li><strong>Edit Berkas</strong>: Arahkan kursor ke nama berkas, klik menu "..." dan pilih "Edit" untuk mengubah nama atau deskripsinya melalui sebuah modal.</li>
                                <li><strong>Hapus Berkas</strong>: Pilih "Hapus" dari menu yang sama. Tindakan ini akan menghapus berkas, dan semua surat di dalamnya akan otomatis kembali ke status "belum diarsipkan" tanpa terhapus.</li>
                             </ul>
                        </div>
                         <div>
                            <h4 class="font-semibold text-gray-800">3. Mengarsipkan dan Memindahkan Surat</h4>
                            <p>Surat dapat diarsipkan atau dipindahkan antar berkas dengan mudah dari beberapa lokasi.</p>
                             <ul class="list-disc list-inside ml-4 space-y-2">
                                <li><strong>Dari Halaman Arsip</strong>: Setiap surat di daftar utama memiliki tombol "Pindahkan". Mengkliknya akan membuka modal untuk memilih berkas tujuan.</li>
                                <li><strong>Dari Halaman Detail Surat</strong>: Saat Anda melihat detail sebuah surat, tersedia tombol "Arsipkan" (jika belum diarsip) atau "Pindahkan" (jika sudah diarsip) yang berfungsi sama.</li>
                                <li><strong>Proses</strong>: Saat surat dipindahkan/diarsipkan, sistem akan mengubah `berkas_id` dan memperbarui status surat menjadi "diarsipkan".</li>
                            </ul>
                        </div>
                        <div>
                            <h4 class="font-semibold text-gray-800">4. Mengakses Isi Berkas</h4>
                             <ul class="list-disc list-inside ml-4 space-y-2">
                                <li><strong>Halaman Detail Berkas</strong>: Mengklik nama berkas di sidebar akan membawa Anda ke halaman khusus yang hanya menampilkan surat-surat di dalam berkas tersebut.</li>
                                <li><strong>Pencarian Spesifik</strong>: Di dalam halaman detail berkas, Anda dapat melakukan pencarian dan pemfilteran lebih lanjut yang hanya berlaku untuk isi berkas itu.</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </x-card>

        </div>
    </div>

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/mermaid@10.3.1/dist/mermaid.min.js"></script>
        <script>
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
