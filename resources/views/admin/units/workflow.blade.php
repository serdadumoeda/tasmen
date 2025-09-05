@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <!-- Page-Title -->
        <div class="row">
            <div class="col-sm-12">
                <div class="page-title-box">
                    <div class="float-end">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('admin.units.index') }}">Manajemen Unit</a></li>
                            <li class="breadcrumb-item active">Alur Kerja</li>
                        </ol>
                    </div>
                    <h4 class="page-title">Alur Kerja Manajemen Unit</h4>
                </div><!--end page-title-box-->
            </div><!--end col-->
        </div>
        <!-- end page title end breadcrumb -->

        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Diagram Alur Kerja Manajemen Unit</h4>
                        <p class="text-muted mb-0">Diagram ini menjelaskan proses pengelolaan data unit kerja oleh Superadmin atau Admin.</p>
                    </div><!--end card-header-->
                    <div class="card-body text-center">
                        <div class="mermaid">
                            graph TD
                                A[Mulai] --> B{Akses Menu<br>Manajemen Unit};
                                B --> C[Sistem Menampilkan<br>Daftar Unit Kerja];
                                C --> D{Pilih Aksi};
                                D --> E[Tambah Unit Baru];
                                D --> F[Edit Unit];
                                D --> G[Hapus Unit];

                                E --> H[Isi Form Data Unit<br>(Nama, Induk Unit, dll)];
                                H --> I{Validasi Data};
                                I -- Valid --> J[Simpan Unit Baru ke Database];
                                I -- Tidak Valid --> K[Tampilkan Pesan Error];
                                J --> C;
                                K --> H;

                                F --> L[Pilih Unit yang Akan Diubah];
                                L --> M[Ubah Data pada Form];
                                M --> N{Validasi Data};
                                N -- Valid --> O[Update Data Unit di Database];
                                N -- Tidak Valid --> P[Tampilkan Pesan Error];
                                O --> C;
                                P --> M;

                                G --> Q[Pilih Unit yang Akan Dihapus];
                                Q --> R{Konfirmasi Hapus};
                                R -- Ya --> S{Pengecekan Ketergantungan<br>(e.g. Pegawai di Unit tsb)};
                                R -- Tidak --> C;
                                S -- Ada Ketergantungan --> T[Tampilkan Pesan Error<br>Hapus Gagal];
                                S -- Tidak Ada Ketergantungan --> U[Hapus Data Unit dari Database];
                                T --> C;
                                U --> C;
                                C --> V[Selesai];


                                style A fill:#28a745,stroke:#333,stroke-width:2px,color:#fff
                                style V fill:#dc3545,stroke:#333,stroke-width:2px,color:#fff
                                style C fill:#17a2b8,stroke:#333,stroke-width:2px,color:#fff
                                style J fill:#4CAF50,stroke:#333,stroke-width:2px,color:#fff
                                style O fill:#4CAF50,stroke:#333,stroke-width:2px,color:#fff
                                style U fill:#4CAF50,stroke:#333,stroke-width:2px,color:#fff
                        </div>
                    </div><!--end card-body-->
                </div><!--end card-->
            </div>
        </div>

        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Deskripsi Alur Kerja</h4>
                        <p class="text-muted mb-0">Penjelasan detail mengenai setiap langkah dalam proses manajemen unit kerja.</p>
                    </div><!--end card-header-->
                    <div class="card-body">
                        <dl class="row">
                            <dt class="col-sm-3">1. Akses Menu</dt>
                            <dd class="col-sm-9">Superadmin atau Admin mengakses menu "Manajemen Unit" dari sidebar navigasi untuk memulai proses pengelolaan unit kerja.</dd>

                            <dt class="col-sm-3">2. Tampilan Daftar Unit</dt>
                            <dd class="col-sm-9">Sistem akan menampilkan halaman yang berisi daftar semua unit kerja yang sudah terdaftar, biasanya dalam format tabel yang informatif.</dd>

                            <dt class="col-sm-3">3. Pilihan Aksi</dt>
                            <dd class="col-sm-9">Pada halaman daftar unit, tersedia beberapa tombol aksi:</dd>
                            <dd class="col-sm-9 offset-sm-3">
                                <ul>
                                    <li><b>Tambah Unit Baru:</b> Untuk membuat unit kerja baru.</li>
                                    <li><b>Edit:</b> Untuk mengubah data unit kerja yang sudah ada.</li>
                                    <li><b>Hapus:</b> Untuk menghapus unit kerja dari sistem.</li>
                                </ul>
                            </dd>

                            <dt class="col-sm-3">4. Proses Tambah Unit</dt>
                            <dd class="col-sm-9">Saat memilih "Tambah Unit", pengguna akan diarahkan ke form pembuatan unit. Setelah mengisi data dan menyimpan, sistem akan memvalidasi input. Jika valid, unit baru akan tersimpan dan daftar unit akan diperbarui. Jika tidak, pesan kesalahan akan ditampilkan.</dd>

                            <dt class="col-sm-3">5. Proses Edit Unit</dt>
                            <dd class="col-sm-9">Pengguna memilih unit yang ingin diubah dan mengklik tombol "Edit". Form akan terisi dengan data unit saat ini. Setelah mengubah data, sistem akan melakukan validasi sebelum menyimpan perubahan.</dd>

                            <dt class="col-sm-3">6. Proses Hapus Unit</dt>
                            <dd class="col-sm-9">Setelah memilih "Hapus" pada unit tertentu, sistem akan meminta konfirmasi. Sebelum benar-benar menghapus, sistem akan memeriksa apakah ada data lain yang bergantung pada unit tersebut (misalnya, pegawai yang terdaftar di unit itu). Jika ada, penghapusan akan dibatalkan untuk menjaga integritas data. Jika tidak ada, unit akan dihapus.</dd>

                            <dt class="col-sm-3">7. Selesai</dt>
                            <dd class="col-sm-9">Semua proses (tambah, edit, hapus) akan berakhir dengan sistem menampilkan kembali daftar unit kerja yang telah diperbarui.</dd>
                        </dl>
                    </div><!--end card-body-->
                </div><!--end card-->
            </div>
        </div>

    </div><!-- container -->
@endsection

@section('scripts')
<script src="{{ asset('assets/plugins/mermaid/mermaid.min.js') }}"></script>
<script>
    mermaid.initialize({ startOnLoad: true });
</script>
@endsection
