<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\File;
use App\Models\User;
use App\Models\Unit;
use App\Models\Jabatan;
use Carbon\Carbon;

class OrganizationalDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        $dbDriver = DB::connection()->getDriverName();

        if ($dbDriver === 'mysql') {
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        } elseif ($dbDriver === 'pgsql') {
            DB::statement("SET session_replication_role = 'replica';");
        }

        User::truncate();
        Jabatan::truncate();
        Unit::truncate();
        DB::table('unit_paths')->truncate();

        $json = File::get(database_path('data/users_profile_data.json'));
        $data = json_decode($json);

        $this->command->getOutput()->progressStart(count($data));

        $unitCache = [];

        foreach ($data as $item) {
            // 1. Create Unit Hierarchy
            $unitFields = [
                'Unit Kerja Eselon I',
                'Unit Kerja Eselon II',
                'Unit Kerja Koordinator',
                'Unit Kerja Sub Koordinator',
            ];

            $unitLevels = [
                'Unit Kerja Eselon I' => Unit::LEVEL_ESELON_I,
                'Unit Kerja Eselon II' => Unit::LEVEL_ESELON_II,
                'Unit Kerja Koordinator' => Unit::LEVEL_KOORDINATOR,
                'Unit Kerja Sub Koordinator' => Unit::LEVEL_SUB_KOORDINATOR,
            ];

            $parentUnitId = null;
            $lastUnitId = null;

            foreach ($unitFields as $field) {
                $unitName = $item->{$field};
                if (empty($unitName)) {
                    continue;
                }

                $cacheKey = $parentUnitId . '_' . $unitName;

                if (isset($unitCache[$cacheKey])) {
                    $unit = $unitCache[$cacheKey];
                } else {
                    $unit = Unit::firstOrCreate(
                        [
                            'name' => $unitName,
                            'parent_unit_id' => $parentUnitId
                        ],
                        [
                            'level' => $unitLevels[$field]
                        ]
                    );
                    $unitCache[$cacheKey] = $unit;
                }

                $parentUnitId = $unit->id;
                $lastUnitId = $unit->id;
            }

            // 2. Create Jabatan
            $jabatan = Jabatan::create([
                'name' => $item->Jabatan,
                'unit_id' => $lastUnitId,
            ]);

            // 3. Determine Role
            $role = Unit::LEVEL_STAF; // Default role
            if (isset($item->Eselon)) {
                switch ($item->Eselon) {
                    case '1-A':
                        $role = Unit::LEVEL_ESELON_I;
                        break;
                    case '2-A':
                        $role = Unit::LEVEL_ESELON_II;
                        break;
                    case '3-A':
                        $role = Unit::LEVEL_KOORDINATOR;
                        break;
                    case '4-A':
                        $role = Unit::LEVEL_SUB_KOORDINATOR;
                        break;
                }
            }

            // 4. Create User
            $user = User::create([
                'nip' => $item->NIP,
                'name' => $item->Nama,
                'email' => strtolower(str_replace(' ', '.', preg_replace('/[^a-zA-Z0-9\s]/', '', $item->Nama))) . '@example.com',
                'password' => Hash::make('password'),
                'unit_id' => $lastUnitId,
                'jabatan_id' => $jabatan->id,
                'role' => $role,
                'status' => 'active',
                'tempat_lahir' => $item->{'Tempat Lahir'},
                'tgl_lahir' => isset($item->{'Tgl. Lahir'}) ? Carbon::createFromFormat('d-m-Y', $item->{'Tgl. Lahir'})->format('Y-m-d') : null,
                'alamat' => $item->Alamat,
                'jenis_kelamin' => $item->{'L/P'},
                'agama' => $item->Agama,
                'golongan' => $item->Gol,
                'eselon' => $item->Eselon,
                'tmt_eselon' => isset($item->{'TMT ESELON'}) ? Carbon::createFromFormat('d-m-Y', $item->{'TMT ESELON'})->format('Y-m-d') : null,
                'grade' => $item->GRADE,
                'no_hp' => $item->{'No. HP'},
                'telepon' => $item->Telepon,
                'npwp' => $item->NPWP,
                'pendidikan_terakhir' => $item->{'Pendidikan Terakhir'},
                'pendidikan_jurusan' => $item->{'Unnamed: 17'},
                'pendidikan_universitas' => $item->{'Unnamed: 18'},
                'jenis_jabatan' => $item->{'Jenis Jabatan'},
                'tmt_cpns' => isset($item->{'TMT CPNS'}) ? Carbon::createFromFormat('d-m-Y', $item->{'TMT CPNS'})->format('Y-m-d') : null,
                'tmt_pns' => isset($item->{'TMT PNS'}) ? Carbon::createFromFormat('d-m-Y', $item->{'TMT PNS'})->format('Y-m-d') : null,
            ]);

            // Assign user to jabatan
            $jabatan->user_id = $user->id;
            $jabatan->save();

            $this->command->getOutput()->progressAdvance();
        }

        // Rebuild the hierarchy paths
        Unit::rebuildPaths();

        // Set Atasan ID
        $this->setAtasan();

        if ($dbDriver === 'mysql') {
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        } elseif ($dbDriver === 'pgsql') {
            DB::statement("SET session_replication_role = 'origin';");
        }

        $this->command->getOutput()->progressFinish();
    }

    private function setAtasan()
    {
        $users = User::with('unit.parentUnit.jabatans')->get();
        foreach($users as $user) {
            if($user->unit && $user->unit->parentUnit) {
                // Find the user who holds the main position in the parent unit
                $atasan = User::where('unit_id', $user->unit->parent_unit_id)
                                ->whereIn('role', [Unit::LEVEL_ESELON_I, Unit::LEVEL_ESELON_II, Unit::LEVEL_KOORDINATOR, Unit::LEVEL_SUB_KOORDINATOR])
                                ->first();

                if($atasan) {
                    $user->atasan_id = $atasan->id;
                    $user->save();
                }
            }
        }
    }
}
