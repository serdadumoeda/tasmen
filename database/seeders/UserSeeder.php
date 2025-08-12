<?php

namespace Database\Seeders;

use App\Models\Unit;
use App\Models\User;
use App\Models\Jabatan;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // 1. PREPARE DATABASE
        $this->command->info('Truncating tables...');
        Schema::disableForeignKeyConstraints();
        User::truncate();
        Jabatan::truncate();
        Unit::truncate();
        DB::table('unit_paths')->truncate();
        Schema::enableForeignKeyConstraints();

        // 2. READ JSON DATA
        $this->command->info('Reading JSON data...');
        $jsonPath = database_path('data/users.json');
        if (!File::exists($jsonPath)) {
            $this->command->error('users.json file not found!');
            return;
        }
        $json = File::get($jsonPath);
        $data = json_decode($json);

        // 3. SEED DATA
        $this->command->info('Seeding units, jabatans, and users...');
        $bar = $this->command->getOutput()->createProgressBar(count($data));

        $atasanMapping = [];

        foreach ($data as $item) {
            if (empty($item->Nama) || empty($item->Jabatan) || empty($item->NIP)) {
                $bar->advance();
                continue; // Skip records without essential info
            }

            // --- Create Unit Hierarchy ---
            $parentUnitId = null;
            $unitHierarchy = $item->unit_kerja;
            $unitLevels = [
                'kementerian' => Unit::LEVEL_MENTERI,
                'eselon_1' => Unit::LEVEL_ESELON_I,
                'eselon_2' => Unit::LEVEL_ESELON_II,
                'koordinator' => Unit::LEVEL_KOORDINATOR,
                'sub_koordinator' => Unit::LEVEL_SUB_KOORDINATOR,
            ];

            $currentUnit = null;
            foreach ($unitLevels as $levelKey => $levelName) {
                $unitName = $unitHierarchy->{$levelKey};
                if (!empty($unitName) && strtolower($unitName) !== 'nan') {
                    $currentUnit = Unit::firstOrCreate(
                        ['name' => $unitName, 'parent_unit_id' => $parentUnitId],
                        ['level' => $levelName]
                    );
                    $parentUnitId = $currentUnit->id;
                }
            }

            $finalUnit = $currentUnit;
            if (!$finalUnit) {
                $bar->advance();
                continue;
            }

            // --- Create User ---
            $user = User::create([
                'name' => $item->Nama,
                'email' => $item->NIP . '@naker.go.id', // Create a unique email
                'password' => Hash::make('password'),
                'unit_id' => $finalUnit->id,
                'role' => $finalUnit->level,
                'status' => 'active',

                // New profile fields
                'nip' => $item->NIP,
                'tempat_lahir' => $item->{'Tempat Lahir'},
                'alamat' => $item->Alamat,
                'tgl_lahir' => $item->{'Tgl. Lahir'},
                'jenis_kelamin' => $item->{'L/P'},
                'golongan' => $item->Gol,
                'eselon' => $item->Eselon,
                'tmt_eselon' => $item->{'TMT ESELON'},
                'grade' => $item->GRADE,
                'agama' => $item->Agama,
                'telepon' => $item->Telepon,
                'no_hp' => $item->{'No. HP'},
                'npwp' => $item->NPWP,
                'tmt_gol' => $item->{'TMT GOL'},
                'pendidikan_terakhir' => $item->{'Pendidikan Terakhir'},
                'jenis_jabatan' => $item->{'Jenis Jabatan'},
                'tmt_cpns' => $item->{'TMT CPNS'},
                'tmt_pns' => $item->{'TMT PNS'},
                'tmt_jabatan' => $item->{'TMT JABATAN'},
            ]);

            // --- Create Jabatan and link it to the user ---
            Jabatan::create([
                'name' => $item->Jabatan,
                'unit_id' => $finalUnit->id,
                'user_id' => $user->id,
            ]);

            // Store the created user for atasan mapping
            $atasanMapping[$item->{'Unit Kerja'}] = $user->id;

            $bar->advance();
        }

        $bar->finish();
        $this->command->info("\nData seeding completed.");

        // 4. SET ATASAN (SUPERVISOR)
        $this->command->info('Setting up supervisor relationships...');
        $allUsers = User::whereNotNull('nip')->get()->keyBy('nip');

        foreach ($data as $item) {
            if (empty($item->NIP)) continue;

            $user = $allUsers->get($item->NIP);
            if (!$user) continue;

            $unitKerjaParts = explode(' - ', $item->{'Unit Kerja'});
            // The atasan's unit is one level up.
            array_pop($unitKerjaParts);
            $atasanUnitKerja = implode(' - ', $unitKerjaParts);

            if (isset($atasanMapping[$atasanUnitKerja])) {
                $atasanId = $atasanMapping[$atasanUnitKerja];
                if ($user->id !== $atasanId) {
                    $user->atasan_id = $atasanId;
                    $user->save();
                }
            }
        }
        $this->command->info('Supervisor relationships set.');

        // 5. REBUILD HIERARCHY PATHS
        $this->command->info('Rebuilding unit hierarchy paths...');
        Unit::rebuildPaths();
        $this->command->info('Hierarchy paths rebuilt successfully.');
    }
}
