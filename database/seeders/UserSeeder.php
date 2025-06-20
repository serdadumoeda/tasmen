<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema; // <-- TAMBAHKAN BARIS INI

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        // PERBAIKAN: Gunakan metode Laravel yang kompatibel dengan banyak database
        Schema::disableForeignKeyConstraints();
        User::truncate();
        Schema::enableForeignKeyConstraints();

        // 1. SUPER ADMIN (Non-hierarki)
        $superadmin = User::create([
            'name' => 'Super Admin',
            'email' => 'superadmin@example.com',
            'password' => Hash::make('password'),
            'role' => 'superadmin',
            'parent_id' => null,
        ]);

        // 2. KEPALA BADAN (ESELON I)
        $kepalaBadan = User::create([
            'name' => 'Kepala Badan',
            'email' => 'kepala.badan@example.com',
            'password' => Hash::make('password'),
            'role' => 'Eselon I',
            'parent_id' => null,
        ]);

        // 3. ESELON II (Di bawah Kepala Badan)
        $sekban = User::create(['name' => 'Sekretaris Badan Perencanaan dan Pengembangan Ketenagakerjaan', 'email' => 'sekban@example.com', 'password' => Hash::make('password'), 'role' => 'Eselon II', 'parent_id' => $kepalaBadan->id]);
        $kapusren = User::create(['name' => 'Kepala Pusat Perencanaan Ketenagakerjaan', 'email' => 'kapusren@example.com', 'password' => Hash::make('password'), 'role' => 'Eselon II', 'parent_id' => $kepalaBadan->id]);
        $kapusdatik = User::create(['name' => 'Kepala Pusat Data dan TI Ketenagakerjaan', 'email' => 'kapusdatik@example.com', 'password' => Hash::make('password'), 'role' => 'Eselon II', 'parent_id' => $kepalaBadan->id]);
        $kapusbangjak = User::create(['name' => 'Kepala Pusat Pengembangan Kebijakan Ketenagakerjaan', 'email' => 'kapusbangjak@example.com', 'password' => Hash::make('password'), 'role' => 'Eselon II', 'parent_id' => $kepalaBadan->id]);

        // =========================================================================
        // 4. STRUKTUR DI BAWAH SEKRETARIAT BADAN (SETBARENBANG)
        // =========================================================================
        $koorUmum = User::create(['name' => 'Kepala Bagian Umum dan Rumah Tangga', 'email' => 'kabag.umum@example.com', 'password' => Hash::make('password'), 'role' => 'Koordinator', 'parent_id' => $sekban->id]);
        $koorRenprogev = User::create(['name' => 'Koordinator Bid. Penyusunan Rencana, Program, Anggaran, Evaluasi, dan Pelaporan', 'email' => 'koor.renprogev@example.com', 'password' => Hash::make('password'), 'role' => 'Koordinator', 'parent_id' => $sekban->id]);
        $koorKeu = User::create(['name' => 'Koordinator Bidang Pengelolaan Keuangan', 'email' => 'koor.keu@example.com', 'password' => Hash::make('password'), 'role' => 'Koordinator', 'parent_id' => $sekban->id]);
        $koorSDMA = User::create(['name' => 'Koord. Bid. Pengelolaan SDMA, Ortala, RB, P2UUKS, Persuratan, dan Kearsipan', 'email' => 'koor.sdma@example.com', 'password' => Hash::make('password'), 'role' => 'Koordinator', 'parent_id' => $sekban->id]);

        // Ketua Tim (di bawah Koordinator)
        User::create(['name' => 'Ketua Tim Rencana dan Program', 'email' => 'k팀.renprogev@example.com', 'password' => Hash::make('password'), 'role' => 'Ketua Tim', 'parent_id' => $koorRenprogev->id]);
        User::create(['name' => 'Ketua Tim Anggaran', 'email' => 'k팀.keu@example.com', 'password' => Hash::make('password'), 'role' => 'Ketua Tim', 'parent_id' => $koorKeu->id]);
        
        // Sub Koordinator (di bawah Koordinator masing-masing)
        User::create(['name' => 'Kepala Sub Bagian Rumah Tangga dan Perlengkapan', 'email' => 'subbag.rt@example.com', 'password' => Hash::make('password'), 'role' => 'Sub Koordinator', 'parent_id' => $koorUmum->id]);
        User::create(['name' => 'Sub Koord. Bidang Penyusunan Rencana, Program, dan Anggaran', 'email' => 'subkoor.renprog@example.com', 'password' => Hash::make('password'), 'role' => 'Sub Koordinator', 'parent_id' => $koorRenprogev->id]);
        User::create(['name' => 'Sub Koord. Bidang Evaluasi dan Pelaporan', 'email' => 'subkoor.evlap@example.com', 'password' => Hash::make('password'), 'role' => 'Sub Koordinator', 'parent_id' => $koorRenprogev->id]);
        User::create(['name' => 'Sub Koord. Bidang Pelaksanaan Anggaran', 'email' => 'subkoor.pelaksanaan@example.com', 'password' => Hash::make('password'), 'role' => 'Sub Koordinator', 'parent_id' => $koorKeu->id]);
        User::create(['name' => 'Sub Koord. Bidang Perbendaharaan dan Tata Usaha Keuangan', 'email' => 'subkoor.perbendaharaan@example.com', 'password' => Hash::make('password'), 'role' => 'Sub Koordinator', 'parent_id' => $koorKeu->id]);
        User::create(['name' => 'Sub Koord. Bidang Akuntansi dan Pelaporan Keuangan', 'email' => 'subkoor.aklappen@example.com', 'password' => Hash::make('password'), 'role' => 'Sub Koordinator', 'parent_id' => $koorKeu->id]);
        User::create(['name' => 'Sub Koord. Bidang Pengelolaan SDM', 'email' => 'subkoor.sdm@example.com', 'password' => Hash::make('password'), 'role' => 'Sub Koordinator', 'parent_id' => $koorSDMA->id]);
        User::create(['name' => 'Sub Koord. Bidang Ortala dan Reformasi Birokrasi', 'email' => 'subkoor.ortala@example.com', 'password' => Hash::make('password'), 'role' => 'Sub Koordinator', 'parent_id' => $koorSDMA->id]);
        User::create(['name' => 'Sub Koord. Bidang Penyusunan PerUUan dan Kerjasama', 'email' => 'subkoor.peruu@example.com', 'password' => Hash::make('password'), 'role' => 'Sub Koordinator', 'parent_id' => $koorSDMA->id]);
        User::create(['name' => 'Sub Koord. Bidang Persuratan dan Kearsipan', 'email' => 'subkoor.persuratan@example.com', 'password' => Hash::make('password'), 'role' => 'Sub Koordinator', 'parent_id' => $koorSDMA->id]);


        // =========================================================================
        // 5. STRUKTUR DI BAWAH PUSAT PERENCANAAN (PUSRENAKER)
        // =========================================================================
        User::create(['name' => 'Kepala Subbagian Tata Usaha (Pusrenaker)', 'email' => 'subbagtu.pusrenaker@example.com', 'password' => Hash::make('password'), 'role' => 'Sub Koordinator', 'parent_id' => $kapusren->id]);
        $koorPengukuran = User::create(['name' => 'Koordinator Bid. Pengukuran dan Evaluasi Pembangunan Ketenagakerjaan', 'email' => 'koor.pengukuran@example.com', 'password' => Hash::make('password'), 'role' => 'Koordinator', 'parent_id' => $kapusren->id]);
        $koorMakro = User::create(['name' => 'Koordinator Bid. Perencanaan Ketenagakerjaan Makro', 'email' => 'koor.makro@example.com', 'password' => Hash::make('password'), 'role' => 'Koordinator', 'parent_id' => $kapusren->id]);
        $koorMikro = User::create(['name' => 'Koordinator Bid. Perencanaan Ketenagakerjaan Mikro', 'email' => 'koor.mikro@example.com', 'password' => Hash::make('password'), 'role' => 'Koordinator', 'parent_id' => $kapusren->id]);
        
        User::create(['name' => 'Ketua Tim Perencanaan Makro', 'email' => 'k팀.makro@example.com', 'password' => Hash::make('password'), 'role' => 'Ketua Tim', 'parent_id' => $koorMakro->id]);

        User::create(['name' => 'Sub Koord. Bid. Pengukuran Pembangunan Ketenagakerjaan', 'email' => 'subkoor.pengukuran@example.com', 'password' => Hash::make('password'), 'role' => 'Sub Koordinator', 'parent_id' => $koorPengukuran->id]);
        User::create(['name' => 'Sub Koord. Bid. Evaluasi Pembangunan Ketenagakerjaan', 'email' => 'subkoor.evaluasi@example.com', 'password' => Hash::make('password'), 'role' => 'Sub Koordinator', 'parent_id' => $koorPengukuran->id]);
        User::create(['name' => 'Sub Koord. Bid. Perencanaan Ketenagakerjaan Nasional', 'email' => 'subkoor.rennas@example.com', 'password' => Hash::make('password'), 'role' => 'Sub Koordinator', 'parent_id' => $koorMakro->id]);
        User::create(['name' => 'Sub Koord. Bid. Perencanaan Ketenagakerjaan Daerah', 'email' => 'subkoor.renda@example.com', 'password' => Hash::make('password'), 'role' => 'Sub Koordinator', 'parent_id' => $koorMakro->id]);
        User::create(['name' => 'Sub Koord. Bid. Perencanaan Ketenagakerjaan Perusahan Menengah dan Besar', 'email' => 'subkoor.renmenengah@example.com', 'password' => Hash::make('password'), 'role' => 'Sub Koordinator', 'parent_id' => $koorMikro->id]);
        User::create(['name' => 'Sub Koord. Bid. Perencanaan Ketenagakerjaan Perusahaan Kecil dan Mikro', 'email' => 'subkoor.renkecil@example.com', 'password' => Hash::make('password'), 'role' => 'Sub Koordinator', 'parent_id' => $koorMikro->id]);

        // =========================================================================
        // 6. STRUKTUR DI BAWAH PUSAT DATA & TI (PUSDATIK)
        // =========================================================================
        User::create(['name' => 'Kepala Subbagian Tata Usaha (Pusdatik)', 'email' => 'subbagtu.pusdatik@example.com', 'password' => Hash::make('password'), 'role' => 'Sub Koordinator', 'parent_id' => $kapusdatik->id]);
        $koorData = User::create(['name' => 'Koordinator Bid. Pengelolaan Data Ketenagakerjaan', 'email' => 'koor.data@example.com', 'password' => Hash::make('password'), 'role' => 'Koordinator', 'parent_id' => $kapusdatik->id]);
        $koorDiseminasi = User::create(['name' => 'Koordinator Bid. Diseminasi Data dan Informasi Ketenagakerjaan', 'email' => 'koor.diseminasi@example.com', 'password' => Hash::make('password'), 'role' => 'Koordinator', 'parent_id' => $kapusdatik->id]);
        $koorPengembanganSI = User::create(['name' => 'Koordinator Bid. Pengembangan Sistem Informasi Ketenagakerjaan', 'email' => 'koor.pengembangansi@example.com', 'password' => Hash::make('password'), 'role' => 'Koordinator', 'parent_id' => $kapusdatik->id]);
        $koorInfra = User::create(['name' => 'Koordinator Bid. Pengembangan Infrastuktur Teknologi Ketenagakerjaan serta Keamanan Datik', 'email' => 'koor.infra@example.com', 'password' => Hash::make('password'), 'role' => 'Koordinator', 'parent_id' => $kapusdatik->id]);

        User::create(['name' => 'Ketua Tim Sistem Informasi', 'email' => 'k팀.si@example.com', 'password' => Hash::make('password'), 'role' => 'Ketua Tim', 'parent_id' => $koorPengembanganSI->id]);
        User::create(['name' => 'Ketua Tim Infrastruktur', 'email' => 'k팀.infra@example.com', 'password' => Hash::make('password'), 'role' => 'Ketua Tim', 'parent_id' => $koorInfra->id]);

        User::create(['name' => 'Sub Koord. Bid. Penyediaan Data Ketenagakerjaan', 'email' => 'subkoor.penyediaandata@example.com', 'password' => Hash::make('password'), 'role' => 'Sub Koordinator', 'parent_id' => $koorData->id]);
        User::create(['name' => 'Sub Koord. Bid. Analisis Data Ketenagakerjaan', 'email' => 'subkoor.analisisdata@example.com', 'password' => Hash::make('password'), 'role' => 'Sub Koordinator', 'parent_id' => $koorData->id]);
        User::create(['name' => 'Sub Koord. Bid. Penyajian Data dan Informasi Ketenagakerjaan', 'email' => 'subkoor.penyajiandata@example.com', 'password' => Hash::make('password'), 'role' => 'Sub Koordinator', 'parent_id' => $koorDiseminasi->id]);
        User::create(['name' => 'Sub Koord. Bid. Pelayanan Data dan Informasi Ketenagakerjaan', 'email' => 'subkoor.pelayanandata@example.com', 'password' => Hash::make('password'), 'role' => 'Sub Koordinator', 'parent_id' => $koorDiseminasi->id]);
        User::create(['name' => 'Sub Koord. Bid. Perancangan Sistem Informasi Ketenagakerjaan', 'email' => 'subkoor.rancangsi@example.com', 'password' => Hash::make('password'), 'role' => 'Sub Koordinator', 'parent_id' => $koorPengembanganSI->id]);
        User::create(['name' => 'Sub Koord. Bid. Pengembangan Sistem Informasi Ketenagakerjaan', 'email' => 'subkoor.bangsi@example.com', 'password' => Hash::make('password'), 'role' => 'Sub Koordinator', 'parent_id' => $koorPengembanganSI->id]);
        User::create(['name' => 'Sub Koord. Bid. Pengembangan Infrastruktur T.I.K.', 'email' => 'subkoor.banginfra@example.com', 'password' => Hash::make('password'), 'role' => 'Sub Koordinator', 'parent_id' => $koorInfra->id]);
        User::create(['name' => 'Sub Koord. Bid. Pengembangan Teknologi Keamanan Datik', 'email' => 'subkoor.kamdatik@example.com', 'password' => Hash::make('password'), 'role' => 'Sub Koordinator', 'parent_id' => $koorInfra->id]);
        
        // =========================================================================
        // 7. STRUKTUR DI BAWAH PUSAT PENGEMBANGAN KEBIJAKAN (PUSBANGJAK)
        // =========================================================================
        User::create(['name' => 'Kepala Subbagian Tata Usaha (Pusbangjak)', 'email' => 'subbagtu.pusbangjak@example.com', 'password' => Hash::make('password'), 'role' => 'Sub Koordinator', 'parent_id' => $kapusbangjak->id]);
        $koorLatvokas = User::create(['name' => 'Koordinator Bid. Pengembangan Kebijakan Bid. Latvokas, Produktivitas, Pentakerja, dan PKK', 'email' => 'koor.latvokas@example.com', 'password' => Hash::make('password'), 'role' => 'Koordinator', 'parent_id' => $kapusbangjak->id]);
        $koorWasnaker = User::create(['name' => 'Koordinator Bid. Pengembangan Kebijakan Bid. Wasnaker, K3, HI Jamsos', 'email' => 'koor.wasnaker@example.com', 'password' => Hash::make('password'), 'role' => 'Koordinator', 'parent_id' => $kapusbangjak->id]);
        $koorEvpro = User::create(['name' => 'Koordinator Bid. Pengembangan Kebijakan Bid. Evaluasi Program Prioritas Nasional dan Kementerian', 'email' => 'koor.evpro@example.com', 'password' => Hash::make('password'), 'role' => 'Koordinator', 'parent_id' => $kapusbangjak->id]);
        
        User::create(['name' => 'Ketua Tim Lavotas', 'email' => 'k팀.lavotas@example.com', 'password' => Hash::make('password'), 'role' => 'Ketua Tim', 'parent_id' => $koorLatvokas->id]);
        User::create(['name' => 'Ketua Tim Evaluasi Program', 'email' => 'k팀.evpro@example.com', 'password' => Hash::make('password'), 'role' => 'Ketua Tim', 'parent_id' => $koorEvpro->id]);

        User::create(['name' => 'Sub Koord. Bid. Pengembangan Kebijakan Bidang Lavotas', 'email' => 'subkoor.lavotas@example.com', 'password' => Hash::make('password'), 'role' => 'Sub Koordinator', 'parent_id' => $koorLatvokas->id]);
        User::create(['name' => 'Sub Koord. Bid. Pengembangan Kebijakan Bid. Pentakerja dan PKK', 'email' => 'subkoor.pentakerja@example.com', 'password' => Hash::make('password'), 'role' => 'Sub Koordinator', 'parent_id' => $koorLatvokas->id]);
        User::create(['name' => 'Sub Koord. Bid. Pengembangan Kebijakan Bid. Wasnaker dan K3', 'email' => 'subkoor.wasnakerk3@example.com', 'password' => Hash::make('password'), 'role' => 'Sub Koordinator', 'parent_id' => $koorWasnaker->id]);
        User::create(['name' => 'Sub Koord. Bid. Pengembangan Kebijakan Bid. HI dan Jamsostek', 'email' => 'subkoor.hijamsostek@example.com', 'password' => Hash::make('password'), 'role' => 'Sub Koordinator', 'parent_id' => $koorWasnaker->id]);
        User::create(['name' => 'Sub Koord. Bid. Pengembangan Kebijakan Bid. Evaluasi Program Prioritas Nasional', 'email' => 'subkoor.evpronas@example.com', 'password' => Hash::make('password'), 'role' => 'Sub Koordinator', 'parent_id' => $koorEvpro->id]);
        User::create(['name' => 'Sub Koord. Bid. Pengembangan Kebijakan Bidang Evaluasi Program Prioritas Kementerian', 'email' => 'subkoor.evprokemen@example.com', 'password' => Hash::make('password'), 'role' => 'Sub Koordinator', 'parent_id' => $koorEvpro->id]);
        
        // =========================================================================
        // 8. STAFF (Sebagai contoh di bawah beberapa Sub Koordinator)
        // =========================================================================
        $subkoorBangsi = User::where('email', 'subkoor.bangsi@example.com')->first();
        if ($subkoorBangsi) {
            User::create(['name' => 'Staff Pengembangan SI 1', 'email' => 'staff.bangsi1@example.com', 'password' => Hash::make('password'), 'role' => 'Staff', 'parent_id' => $subkoorBangsi->id]);
            User::create(['name' => 'Staff Pengembangan SI 2', 'email' => 'staff.bangsi2@example.com', 'password' => Hash::make('password'), 'role' => 'Staff', 'parent_id' => $subkoorBangsi->id]);
        }

        $subkoorLavotas = User::where('email', 'subkoor.lavotas@example.com')->first();
        if ($subkoorLavotas) {
            User::create(['name' => 'Staff Analis Lavotas', 'email' => 'staff.lavotas1@example.com', 'password' => Hash::make('password'), 'role' => 'Staff', 'parent_id' => $subkoorLavotas->id]);
        }
    }
}