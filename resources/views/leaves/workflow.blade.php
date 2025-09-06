<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            <i class="fas fa-calendar-check mr-2"></i>
            {{ __('Alur Kerja Modul Manajemen Cuti') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">

            <x-card>
                <div class="p-6">
                    <h3 class="text-xl font-bold text-gray-800 mb-2">Dokumentasi Alur Kerja Cuti</h3>
                    <p class="text-gray-600">Halaman ini menjelaskan proses pengajuan, persetujuan berjenjang, dan penerbitan SK Cuti otomatis.</p>
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
    classDef decision fill:#FDEDEC,stroke:#C0392B;
    classDef notif fill:#F4ECF7,stroke:#8E44AD;

    subgraph sg1 [A. Pengajuan]
        A1(Pegawai):::action --> A2(Form Pengajuan Cuti):::page;
        A2 -- Submit --> A3{Validasi & Cek Saldo}:::decision;
        A3 -- Gagal --> A2;
        A3 -- Sukses --> A4(Simpan Permintaan):::process;
        A4 --> A5(Notifikasi ke Atasan):::notif;
    end

    subgraph sg2 [B. Persetujuan Berjenjang]
        A5 --> B1(Atasan):::action;
        B1 --> B2(Detail Permintaan Cuti):::page;
        B2 -- Setujui --> B3{Ada Jenjang Berikutnya?}:::decision;
        B3 -- Ya --> B4(Teruskan ke Atasan Berikutnya):::process;
        B4 --> B1;
        B3 -- Tidak / Final --> B5(Status: Approved):::process;
        B2 -- Tolak --> B6(Status: Rejected):::process;
    end

    subgraph sg3 [C. Penerbitan SK Otomatis]
        B5 --> C1(Generate Dokumen SK):::process;
        C1 --> C2(Simpan & Tautkan Surat):::process;
        C2 --> C3(Selesai);
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
                            <h4 class="font-semibold text-gray-800">1. Pengajuan Cuti</h4>
                            <p>Pegawai mengajukan cuti melalui form. Sistem akan memvalidasi sisa saldo cuti sebelum menyimpan dan mengirim notifikasi ke atasan.</p>
                        </div>
                        <div>
                            <h4 class="font-semibold text-gray-800">2. Persetujuan Berjenjang</h4>
                            <p>Atasan menerima notifikasi dan dapat menyetujui atau menolak. Jika disetujui, sistem akan memeriksa apakah ada level persetujuan berikutnya sesuai alur kerja yang diatur untuk unit pegawai. Jika ada, permintaan akan diteruskan. Jika tidak, permintaan dianggap disetujui sepenuhnya.</p>
                        </div>
                        <div>
                            <h4 class="font-semibold text-gray-800">3. Penerbitan SK Otomatis</h4>
                            <p>Setelah persetujuan final, sistem secara otomatis membuat dokumen SK Cuti dari template dan menautkannya ke permintaan cuti yang bersangkutan.</p>
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
