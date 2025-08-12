<?php

namespace Database\Seeders;

use App\Models\Unit;
use App\Models\User;
use App\Models\Jabatan;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Throwable;

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
        DB::table('activities')->truncate();
        Schema::enableForeignKeyConstraints();

        // 2. CREATE AND LOGIN AS A SYSTEM USER FOR ACTIVITY LOGGING
        $this->command->info('Preparing system user for activity logging...');
        $systemUser = User::create([
            'name' => 'Super Admin',
            'email' => 'superadmin@example.com',
            'password' => Hash::make('password'),
            'role' => User::ROLE_SUPERADMIN,
            'status' => 'active'
        ]);
        Auth::login($systemUser);

        // 3. READ JSON DATA
        $this->command->info('Reading JSON data...');
        $jsonPath = database_path('data/users.json');
        if (!File::exists($jsonPath)) {
            $this->command->error('users.json file not found!');
            return;
        }
        $json = File::get($jsonPath);
        $data = json_decode($json);

        // 4. FIRST PASS: Create Units, Users, and Jabatans
        $this->command->info('Pass 1/2: Seeding units, jabatans, and users...');
        $bar = $this->command->getOutput()->createProgressBar(count($data));

        $userJabatanMapping = [];

        foreach ($data as $index => $item) {
            try {
                if (empty($item->Nama) || empty($item->Jabatan) || empty($item->NIP)) {
                    continue;
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
                            ['name' => trim($unitName), 'parent_unit_id' => $parentUnitId],
                            ['level' => $levelName]
                        );
                        $parentUnitId = $currentUnit->id;
                    }
                }

                $finalUnit = $currentUnit;
                if (!$finalUnit) {
                    $this->command->warn("\nSkipping user {$item->Nama} due to missing unit info.");
                    continue;
                }

                // --- Determine Role ---
                $role = match (trim($item->Eselon ?? '')) {
                    '1-A' => User::ROLE_ESELON_I,
                    '2-A' => User::ROLE_ESELON_II,
                    '3-A' => User::ROLE_KOORDINATOR,
                    '4-A' => User::ROLE_SUB_KOORDINATOR,
                    default => $finalUnit->level,
                };

                // --- Create User ---
                $user = User::create([
                    'name' => trim($item->Nama),
                    'email' => trim($item->NIP) . '@naker.go.id',
                    'password' => Hash::make('password'),
                    'unit_id' => $finalUnit->id,
                    'role' => $role,
                    'status' => 'active',
                    'nip' => trim($item->NIP),
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

                Jabatan::create([
                    'name' => trim($item->Jabatan),
                    'unit_id' => $finalUnit->id,
                    'user_id' => $user->id,
                ]);

                $userJabatanMapping[trim($item->{'Unit Kerja'})] = $user->id;

            } catch (Throwable $e) {
                $this->command->error("\nFailed to seed user at index {$index} ({$item->Nama}): " . $e->getMessage());
            } finally {
                $bar->advance();
            }
        }

        $bar->finish();
        $this->command->info("\nPass 1/2 completed.");

        // 5. SECOND PASS: SET ATASAN (SUPERVISOR)
        $this->command->info('Pass 2/2: Setting up supervisor relationships...');
        $allUsers = User::whereNotNull('nip')->get()->keyBy('nip');

        foreach ($data as $item) {
            if (empty($item->NIP)) continue;

            $user = $allUsers->get(trim($item->NIP));
            if (!$user) continue;

            $unitKerjaParts = explode(' - ', trim($item->{'Unit Kerja'}));
            if (count($unitKerjaParts) > 1) {
                array_pop($unitKerjaParts);
                $atasanUnitKerja = implode(' - ', $unitKerjaParts);

                if (isset($userJabatanMapping[$atasanUnitKerja])) {
                    $atasanId = $userJabatanMapping[$atasanUnitKerja];
                    if ($user->id !== $atasanId) {
                        $user->atasan_id = $atasanId;
                        $user->save();
                    }
                }
            }
        }
        $this->command->info('Supervisor relationships set.');

        // 6. REBUILD HIERARCHY PATHS
        $this->command->info('Rebuilding unit hierarchy paths...');
        Unit::rebuildPaths();
        $this->command->info('Hierarchy paths rebuilt successfully.');

        Auth::logout();
    }
}
