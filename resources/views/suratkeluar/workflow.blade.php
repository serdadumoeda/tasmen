<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            <i class="fas fa-paper-plane mr-2"></i>
            {{ __('Alur Kerja Modul Surat Keluar') }}
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
                    <h3 class="text-xl font-bold text-gray-800 mb-2">Dokumentasi Alur Kerja Surat Keluar</h3>
                    <p class="text-gray-600">Halaman ini menjelaskan proses pembuatan, persetujuan, dan tindak lanjut untuk Surat Keluar.</p>
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

    subgraph sg1 [A. Pembuatan Surat]
        A1(Mulai) --> A2(Pilih Metode):::page;
        A2 -- Dari Template --> A3(Pilih Template & Isi Konten):::page;
        A2 -- Unggah Manual --> A4(Unggah PDF):::page;
        A3 --> A5(Simpan Draf & Generate Nomor):::process;
        A4 --> A5;
    end

    subgraph sg2 [B. Persetujuan & TTE]
        A5 --> B1(Detail Surat Draft):::page;
        B1 --> B2(Pejabat Setujui Surat):::action;
        B2 --> B3{Gunakan TTE?}:::decision;
        B3 -- Ya --> B4(Panggil TTE Service):::process;
        B3 -- Tidak --> B5(Status Disetujui):::process;
        B4 --> B5;
    end

    subgraph sg3 [C. Tindak Lanjut]
        B5 --> C1(Surat Final Siap):::page;
        C1 --> C2(Unduh / Arsipkan):::action;
        C1 --> C3(Buat SK Penugasan):::action;
        C3 --> C4(Redirect ke Form SK):::page;
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
                            <h4 class="font-semibold text-gray-800">1. Pembuatan Surat</h4>
                            <p>Pengguna dapat membuat surat keluar melalui dua cara: menggunakan template yang ada atau mengunggah file PDF yang sudah jadi. Setelah disimpan sebagai draf, sistem akan otomatis memberikan nomor surat.</p>
                        </div>
                        <div>
                            <h4 class="font-semibold text-gray-800">2. Persetujuan</h4>
                            <p>Pejabat yang berwenang akan meninjau draf surat. Saat menyetujui, mereka dapat memilih untuk menggunakan Tanda Tangan Elektronik (TTE), yang akan memanggil service eksternal untuk menambahkan QR Code pada dokumen.</p>
                        </div>
                        <div>
                            <h4 class="font-semibold text-gray-800">3. Tindak Lanjut</h4>
                            <p>Surat yang telah disetujui dapat diunduh, diarsipkan, atau digunakan sebagai dasar untuk membuat SK Penugasan baru, yang akan mengarahkan pengguna ke form SK dengan data yang sudah terisi sebagian.</p>
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
