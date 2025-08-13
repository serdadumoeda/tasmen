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
use Carbon\Carbon;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            // Temporarily disable foreign key checks
            Schema::disableForeignKeyConstraints();

            // Truncate tables to ensure a clean slate
            User::truncate();
            Jabatan::truncate();
            Unit::truncate();
            DB::table('unit_paths')->truncate(); // Manually truncate pivot table if it exists

            // Re-enable foreign key checks
            Schema::enableForeignKeyConstraints();

            // Create Super Admin
            User::create([
                'name' => 'Super Admin',
                'email' => 'superadmin@example.com',
                'password' => Hash::make('password'),
                'role' => User::ROLE_SUPERADMIN,
                'status' => User::STATUS_ACTIVE,
            ]);

            $jsonPath = database_path('data/users.json');
            if (!File::exists($jsonPath)) {
                $this->command->error("users.json not found in database/data directory.");
                return;
            }

            $jsonData = File::get($jsonPath);
            $usersData = json_decode($jsonData, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                $this->command->error("Error decoding users.json: " . json_last_error_msg());
                return;
            }

            if (empty($usersData)) {
                $this->command->warn("No data found in users.json or there was a parsing error.");
                return;
            }

            $this->command->info("Starting to seed users and organizational structure...");

            $createdUsers = [];
            $unitCache = [];
            $jabatanCache = [];

            // First pass: Create Units, Jabatans, and Users
            foreach ($usersData as $data) {
                // 1. Create or retrieve Units hierarchically
                $unitParts = array_map('trim', explode('-', $data['Unit Kerja']));
                $parentUnitId = null;
                $currentUnit = null;

                foreach ($unitParts as $part) {
                    $cacheKey = $parentUnitId . '_' . $part;
                    if (!isset($unitCache[$cacheKey])) {
                        $currentUnit = Unit::firstOrCreate(
                            ['name' => $part, 'parent_unit_id' => $parentUnitId]
                        );
                        $unitCache[$cacheKey] = $currentUnit->id;
                    }
                    $parentUnitId = $unitCache[$cacheKey];
                }
                $finalUnit = $currentUnit;

                // 2. Create Jabatan
                $jabatan = Jabatan::create([
                    'name' => $data['Jabatan'],
                    'unit_id' => $finalUnit->id,
                ]);

                // 3. Create User
                $user = User::create([
                    'nip' => $data['NIP'],
                    'name' => $data['Nama'],
                    'email' => strtolower(str_replace(' ', '.', $data['Nama'])) . '@example.com', // Create a dummy email
                    'password' => Hash::make('password'),
                    'role' => $this->getRoleFromEselon($data['Eselon']),
                    'status' => 'active',
                    'unit_id' => $finalUnit->id,
                    'jabatan_id' => $jabatan->id,
                    'tempat_lahir' => $data['Tempat Lahir'],
                    'alamat' => $data['Alamat'],
                    'tgl_lahir' => $this->parseDate($data['Tgl. Lahir']),
                    'jenis_kelamin' => $data['L/P'],
                    'golongan' => $data['Gol'],
                    'eselon' => $data['Eselon'],
                    'tmt_eselon' => $this->parseDate($data['TMT ESELON']),
                    'grade' => $data['GRADE'],
                    'agama' => $data['Agama'],
                    'telepon' => $data['Telepon'],
                    'no_hp' => $data['No. HP'],
                    'npwp' => $data['NPWP'],
                    'pendidikan_terakhir' => $data['Pendidikan Terakhir'],
                    'pendidikan_jurusan' => $data['Unnamed: 17'],
                    'pendidikan_instansi' => $data['Unnamed: 18'],
                    'jenis_jabatan' => $data['Jenis Jabatan'],
                    'tmt_cpns' => $this->parseDate($data['TMT CPNS']),
                    'tmt_pns' => $this->parseDate($data['TMT PNS']),
                ]);

                // Link user to jabatan
                $jabatan->update(['user_id' => $user->id]);

                $createdUsers[] = $user;
            }

            // Second pass: Assign supervisors (atasan_id)
            foreach ($createdUsers as $user) {
                if ($user->unit && $user->unit->parentUnit) {
                    $supervisor = User::where('unit_id', $user->unit->parent_unit_id)
                                      ->whereIn('role', [User::ROLE_ESELON_I, User::ROLE_ESELON_II, User::ROLE_KOORDINATOR, User::ROLE_SUB_KOORDINATOR])
                                      ->orderBy('role', 'asc') // Assuming roles are ordered by hierarchy
                                      ->first();

                    if ($supervisor) {
                        $user->atasan_id = $supervisor->id;
                        $user->save();
                    }
                }
            }

            $this->command->info("Successfully seeded " . count($usersData) . " users.");
        });
    }

    private function getRoleFromEselon($eselon)
    {
        return match ($eselon) {
            '1-A' => User::ROLE_ESELON_I,
            '2-A' => User::ROLE_ESELON_II,
            '3-A' => User::ROLE_KOORDINATOR,
            '4-A' => User::ROLE_SUB_KOORDINATOR,
            default => User::ROLE_STAF,
        };
    }

    private function parseDate($dateString)
    {
        if (empty($dateString)) {
            return null;
        }
        try {
            return Carbon::createFromFormat('d-m-Y', $dateString)->format('Y-m-d');
        } catch (\Exception $e) {
            $this->command->warn("Could not parse date: {$dateString}. Using null instead.");
            return null;
        }
    }
}
