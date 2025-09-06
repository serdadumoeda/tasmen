<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            <i class="fas fa-people-arrows mr-2"></i>
            {{ __('Alur Kerja Penugasan Anggota (Peminjaman)') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">

            <x-card>
                <div class="p-6">
                    <h3 class="text-xl font-bold text-gray-800 mb-2">Dokumentasi Alur Peminjaman Anggota</h3>
                    <p class="text-gray-600">Halaman ini menjelaskan alur kerja formal untuk "meminjam" seorang pegawai dari unit kerja lain (via Resource Pool) untuk ditugaskan ke sebuah proyek.</p>
                </div>
            </x-card>

            <x-card>
                <div class="p-6">
                    <h3 class="text-xl font-bold text-gray-800 mb-4 border-b pb-2">Flowchart Alur Kerja</h3>
                    <p class="text-gray-600 mb-6">Flowchart ini merinci proses dari pengajuan, persetujuan, hingga hasil akhir.</p>
                    <div class="p-4 bg-gray-50 rounded-lg text-center">
                        <pre class="mermaid">
graph TD
    classDef page fill:#EBF5FB,stroke:#3498DB,color:#2874A6;
    classDef action fill:#FEF9E7,stroke:#F1C40F,color:#B7950B;
    classDef process fill:#E8F8F5,stroke:#1ABC9C,color:#148F77;
    classDef decision fill:#FDEDEC,stroke:#C0392B,color:#A93226;
    classDef notif fill:#F4ECF7,stroke:#8E44AD,color:#6C3483;

    subgraph sg1 [A. Pengajuan oleh Manajer Proyek]
        A1(Manajer Proyek):::action --> A2(Pilih Anggota<br>dari Resource Pool):::page;
        A2 --> A3(Ajukan Permintaan Peminjaman):::action;
        A3 --> A4(Sistem Membuat<br>PeminjamanRequest):::process;
        A4 --> A5(Sistem Mencari<br>Atasan Anggota):::process;
        A5 --> A6(Sistem Membuat<br>Draf Surat Peminjaman):::process;
        A6 --> A7(Kirim Notifikasi<br>ke Atasan):::notif;
    end

    subgraph sg2 [B. Persetujuan oleh Atasan Anggota]
        B1(Atasan Anggota):::action --> B2(Buka Notifikasi):::action;
        B2 --> B3(Lihat Detail Permintaan):::page;
        B3 --> B4{Setujui / Tolak?}:::decision;
    end

    subgraph sg3 [C. Hasil Alur]
        B4 -- Setujui --> C1(Update Status: APPROVED):::process;
        C1 --> C2(Tambahkan Anggota<br>ke Tim Proyek):::process;
        C2 --> C3(Finalisasi Surat<br>+ Generate Nomor):::process;
        C3 --> C4(Notifikasi 'Disetujui'<br>ke Manajer Proyek):::notif;

        B4 -- Tolak --> D1(Isi Alasan Penolakan):::page;
        D1 --> D2(Update Status: REJECTED):::process;
        D2 --> D3(Notifikasi 'Ditolak'<br>ke Manajer Proyek):::notif;
    end

    A7 --> B1;

                        </pre>
                    </div>
                </div>
            </x-card>

            <x-card>
                <div class="p-6">
                    <h3 class="text-xl font-bold text-gray-800 mb-4 border-b pb-2">Deskripsi Alur Kerja</h3>
                    <div class="prose max-w-none text-gray-700 space-y-4">
                        <div>
                            <h4 class="font-semibold text-gray-800">1. Pengajuan Peminjaman</h4>
                            <p>Proses dimulai ketika seorang Manajer Proyek membutuhkan tenaga ahli dari unit lain yang tersedia di dalam Resource Pool.</p>
                            <ul class="list-disc list-inside ml-4 space-y-2">
                                <li><strong>Aksi</strong>: Manajer Proyek memilih anggota dari Resource Pool dan mengajukan permintaan peminjaman.</li>
                                <li><strong>Proses Otomatis Sistem</strong>:
                                    <ul class="list-disc list-inside ml-6">
                                        <li>Membuat catatan `PeminjamanRequest` baru dengan status "Pending".</li>
                                        <li>Secara cerdas mencari atasan dari anggota yang diminta (Koordinator atau Eselon II) untuk dijadikan approver.</li>
                                        <li>Membuat draf surat peminjaman resmi yang terasosiasi dengan permintaan ini.</li>
                                        <li>Mengirimkan notifikasi ke approver yang telah ditentukan.</li>
                                    </ul>
                                </li>
                            </ul>
                        </div>
                        <div>
                            <h4 class="font-semibold text-gray-800">2. Proses Persetujuan</h4>
                            <p>Atasan dari anggota yang diminta memegang peran kunci dalam menyetujui atau menolak permintaan.</p>
                             <ul class="list-disc list-inside ml-4 space-y-2">
                                <li><strong>Persetujuan</strong>: Jika disetujui, anggota tersebut akan otomatis ditambahkan ke tim proyek, status permintaan diubah menjadi "Approved", dan draf surat peminjaman akan difinalisasi lengkap dengan nomor surat resmi. Notifikasi persetujuan dikirim kembali ke Manajer Proyek.</li>
                                <li><strong>Penolakan</strong>: Jika ditolak, atasan wajib memberikan alasan. Status permintaan diubah menjadi "Rejected" dan notifikasi penolakan (beserta alasannya) dikirim ke Manajer Proyek.</li>
                            </ul>
                        </div>
                         <div>
                            <h4 class="font-semibold text-gray-800">3. Halaman "Penugasan Anggota"</h4>
                             <p>Halaman ini berfungsi sebagai dasbor personal bagi setiap pengguna untuk melacak semua aktivitas peminjaman yang relevan bagi mereka, yang terbagi menjadi tiga bagian:</p>
                             <ul class="list-disc list-inside ml-4 space-y-2">
                                 <li><strong>Perlu Persetujuan Saya</strong>: Menampilkan daftar permintaan yang menunggu keputusan dari Anda (jika Anda adalah seorang atasan).</li>
                                 <li><strong>Riwayat Permintaan Saya</strong>: Menampilkan daftar permintaan yang telah Anda ajukan ke orang lain.</li>
                                 <li><strong>Riwayat Persetujuan Saya</strong>: Menampilkan daftar permintaan dari orang lain yang telah Anda proses (setujui atau tolak).</li>
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
