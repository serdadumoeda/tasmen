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
use Carbon\Carbon;

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
        $data = json_decode($json, true); // Decode as an associative array

        if (empty($data)) {
            $this->command->error('No data found in users.json or there was a parsing error.');
            return;
        }

        // 4. FIRST PASS: Create Units, Users, and Jabatans
        $this->command->info('Pass 1/2: Seeding units, jabatans, and users...');
        $bar = $this->command->getOutput()->createProgressBar(count($data));

        $userJabatanMapping = [];
        $unitToHeadUserMapping = [];

        foreach ($data as $index => $item) {
            try {
                $nip = trim($item['NIP']);
                if (empty($item['Nama']) || empty($item['Jabatan']) || empty($nip)) {
                    $this->command->warn("\nSkipping user at index {$index} due to missing essential data.");
                    continue;
                }

                // --- Create Unit Hierarchy from specific fields ---
                $parentUnitId = null;
                $unitHierarchyLevels = [
                    'Unit Kerja (Induk)' => Unit::LEVEL_MENTERI,
                    'Unit Kerja Eselon I' => Unit::LEVEL_ESELON_I,
                    'Unit Kerja Eselon II' => Unit::LEVEL_ESELON_II,
                    'Unit Kerja Koordinator' => Unit::LEVEL_KOORDINATOR,
                    'Unit Kerja Sub Koordinator' => Unit::LEVEL_SUB_KOORDINATOR,
                ];

                $lastCreatedUnitId = null;
                foreach ($unitHierarchyLevels as $key => $level) {
                    $unitName = trim($item[$key] ?? '');
                    if (!empty($unitName) && strtolower($unitName) !== 'null' && strtolower($unitName) !== 'nan') {
                        $unit = Unit::firstOrCreate(
                            ['name' => $unitName, 'parent_unit_id' => $parentUnitId],
                            ['level' => $level]
                        );
                        $parentUnitId = $unit->id;
                        $lastCreatedUnitId = $unit->id;
                    }
                }

                $finalUnitId = $lastCreatedUnitId;
                if (!$finalUnitId) {
                    $this->command->warn("\nSkipping user {$item['Nama']} due to missing unit info.");
                    continue;
                }

                // --- Determine Role ---
                $role = match (trim($item['Eselon'] ?? '')) {
                    '1-A' => User::ROLE_ESELON_I,
                    '2-A' => User::ROLE_ESELON_II,
                    '3-A' => User::ROLE_KOORDINATOR,
                    '4-A' => User::ROLE_SUB_KOORDINATOR,
                    default => Unit::find($finalUnitId)->level,
                };

                // --- Create User ---
                $user = User::create([
                    'name' => trim($item['Nama']),
                    'email' => $nip . '@naker.go.id',
                    'password' => Hash::make('password'),
                    'unit_id' => $finalUnitId,
                    'role' => $role,
                    'status' => 'active',
                    'nip' => $nip,
                    'tempat_lahir' => $item['Tempat Lahir'],
                    'alamat' => $item['Alamat'],
                    'tgl_lahir' => $this->formatDate($item['Tgl. Lahir']),
                    'jenis_kelamin' => $item['L/P'],
                    'golongan' => $item['Gol'],
                    'eselon' => $item['Eselon'],
                    'tmt_eselon' => $this->formatDate($item['TMT ESELON']),
                    'grade' => $item['GRADE'],
                    'agama' => $item['Agama'],
                    'telepon' => $item['Telepon'],
                    'no_hp' => $item['No. HP'],
                    'npwp' => $item['NPWP'],
                    'tmt_gol' => null, // Not in JSON
                    'pendidikan_terakhir' => $item['Pendidikan Terakhir'],
                    'jenis_jabatan' => $item['Jenis Jabatan'],
                    'tmt_cpns' => $this->formatDate($item['TMT CPNS']),
                    'tmt_pns' => $this->formatDate($item['TMT PNS']),
                    'tmt_jabatan' => null, // Not in JSON
                ]);

                Jabatan::create([
                    'name' => trim($item['Jabatan']),
                    'unit_id' => $finalUnitId,
                    'user_id' => $user->id,
                ]);

                // Store mapping for supervisor lookup
                $userJabatanMapping[$nip] = ['user_id' => $user->id, 'unit_id' => $finalUnitId];

                // If user is a head of a unit, map their user_id to the unit_id
                if (str_contains(strtolower($item['Jabatan']), 'kepala') || str_contains(strtolower($item['Jabatan']), 'sekretaris')) {
                    $unitToHeadUserMapping[$finalUnitId] = $user->id;
                }

            } catch (Throwable $e) {
                $this->command->error("\nFailed to seed user at index {$index} ({$item['Nama']}): " . $e->getMessage());
            } finally {
                $bar->advance();
            }
        }

        $bar->finish();
        $this->command->info("\nPass 1/2 completed.");

        // 5. SECOND PASS: SET ATASAN (SUPERVISOR)
        $this->command->info('Pass 2/2: Setting up supervisor relationships...');
        $allUsers = User::with('unit')->get();
        $bar2 = $this->command->getOutput()->createProgressBar($allUsers->count());

        foreach($allUsers as $user) {
            if ($user->unit && $user->unit->parent_unit_id) {
                $parentUnitId = $user->unit->parent_unit_id;
                // Find the head of the parent unit
                $atasanId = $unitToHeadUserMapping[$parentUnitId] ?? null;

                if ($atasanId && $user->id !== $atasanId) {
                    $user->atasan_id = $atasanId;
                    $user->save();
                }
            }
            $bar2->advance();
        }

        $bar2->finish();
        $this->command->info("\nSupervisor relationships set.");

        // 6. REBUILD HIERARCHY PATHS
        $this->command->info('Rebuilding unit hierarchy paths...');
        Unit::rebuildPaths();
        $this->command->info('Hierarchy paths rebuilt successfully.');

        Auth::logout();
    }

    /**
     * Format date from d-m-Y to Y-m-d.
     * Handles null, empty, or invalid date strings.
     */
    private function formatDate($dateString): ?string
    {
        if (empty($dateString) || strtolower($dateString) === 'null') {
            return null;
        }
        try {
            return Carbon::createFromFormat('d-m-Y', trim($dateString))->format('Y-m-d');
        } catch (\Exception $e) {
            // Also try with another format if the first fails
            try {
                return Carbon::createFromFormat('d/m/Y', trim($dateString))->format('Y-m-d');
            } catch (\Exception $e2) {
                $this->command->warn("Could not parse date: {$dateString}");
                return null;
            }
        }
    }
}
