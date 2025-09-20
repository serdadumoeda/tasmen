<?php

namespace App\Services;

use App\Models\Role;
use App\Models\User;
use App\Models\Unit;
use App\Models\Jabatan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class OrganizationalDataImporterService
{
    private $unitCache = [];
    private $userCache = [];
    private $roleCache = [];
    private $command;

    public function __construct($command = null)
    {
        $this->command = $command;
    }

    public function processData(array $data): void
    {
        $this->logInfo('Starting data processing...');
        if ($this->command) {
            $this->command->getOutput()->progressStart(count($data));
        }

        // Cache existing users to avoid re-hashing passwords on updates.
        $this->userCache = User::pluck('id', 'nip')->toArray();
        $this->unitCache = Unit::pluck('id', 'name')->toArray();

        foreach ($data as $item) {
            try {
                DB::transaction(function () use ($item) {
                    $this->processItem($item);
                });
            } catch (\Exception $e) {
                $this->logError('Failed to process item for NIP ' . ($item->NIP ?? 'N/A') . ': ' . $e->getMessage());
            }

            if ($this->command) {
                $this->command->getOutput()->progressAdvance();
            }
        }
        
        // After all units and users are created, we can safely update supervisors
        // without worrying about a recursive loop during the creation process.
        $this->updateSupervisorForAllUsers();
        Unit::rebuildPaths();

        if ($this->command) {
            $this->command->getOutput()->progressFinish();
        }
        $this->logInfo('Data processing finished.');
    }

    private function processItem(object $item): void
    {
        // 1. Get or Create Unit
        $unit = $this->getOrCreateUnit($item);
        if (!$unit) {
            $this->logInfo('Skipping item due to missing unit information for NIP: ' . $item->NIP);
            return;
        }

        // 2. Determine Role
        $role = $this->determineRole($item, $unit);
        if (!$role) {
            $this->logError('Could not determine a valid role for NIP: ' . $item->NIP);
            return;
        }

        // 3. Prepare User Data (role is no longer passed here)
        $userData = $this->prepareUserData($item, $unit->id);

        // 4. Update or Create User
        $user = User::updateOrCreate(
            ['nip' => $item->NIP],
            $userData
        );
        // Sync the user's role
        $user->roles()->sync([$role->id]);

        // 5. Get or Create Jabatan and link to User
        $this->getOrCreateJabatan($item, $unit->id, $user->id, $role->name);

        // 6. If user is a structural head, update the unit
        if ($this->isStructuralHead($user)) {
            $unit->kepala_unit_id = $user->id;
            $unit->save();
        }
    }

    private function getOrCreateUnit(object $item): ?Unit
    {
        // First, ensure the root "Kementerian" unit exists.
        $rootUnitName = 'Kementerian Ketenagakerjaan';
        $rootUnit = Unit::firstOrCreate(
            ['name' => $rootUnitName, 'parent_unit_id' => null],
            ['type' => 'Struktural']
        );

        $unitFields = [
            'Unit Kerja Eselon I', 'Unit Kerja Eselon II',
            'Unit Kerja Koordinator', 'Unit Kerja Sub Koordinator'
        ];

        // Determine the type based on "Jenis Jabatan". Default to Fungsional.
        $unitType = (isset($item->{'Jenis Jabatan'}) && $item->{'Jenis Jabatan'} === 'Struktural')
            ? 'Struktural'
            : 'Fungsional';

        // Start the hierarchy from the root unit.
        $parentUnitId = $rootUnit->id;
        $lastUnit = $rootUnit; // Default to root unit if no other is specified.

        foreach ($unitFields as $field) {
            $unitName = trim($item->{$field} ?? '');
            if (empty($unitName)) continue;

            // Use updateOrCreate to ensure the type is set, even if the unit already exists.
            $unit = Unit::updateOrCreate(
                ['name' => $unitName, 'parent_unit_id' => $parentUnitId],
                ['type' => $unitType]
            );

            $parentUnitId = $unit->id;
            $lastUnit = $unit;
        }
        return $lastUnit;
    }

    private function determineRole(object $item, Unit $unit): ?Role
    {
        if (empty($this->roleCache)) {
            $this->roleCache = Role::all()->keyBy('name');
        }

        $roleName = null;
        // Priority 1: Use the explicit "Eselon" field if it's a structural one.
        if (!empty($item->Eselon)) {
            $roleName = match ($item->Eselon) {
                '1-A' => 'Eselon I',
                '2-A' => 'Eselon II',
                '3-A' => 'Eselon III',
                '4-A' => 'Eselon IV',
                default => null,
            };
        }

        // Priority 2: If no structural role, infer from unit depth.
        if (!$roleName) {
            // The `ancestors()` method includes the unit itself. With the root 'Kementerian' unit, depths are shifted by 1.
            // Depth 1: Kementerian, Depth 2: Eselon I, etc.
            $depth = $unit->ancestors()->count();
            $roleName = match ($depth) {
                2 => 'Eselon I',
                3 => 'Eselon II',
                4 => 'Koordinator', // Ananto Wijoyo's unit will now have depth 4.
                5 => 'Sub Koordinator',
                default => 'Staf',
            };
        }

        return $this->roleCache[$roleName] ?? null;
    }

    private function prepareUserData(object $item, int $unitId): array
    {
        $baseEmail = strtolower(str_replace(' ', '.', preg_replace('/[^a-zA-Z0-9\s]/', '', $item->Nama))) . '@example.com';
        $email = $baseEmail;
        $counter = 1;
        while (User::where('email', $email)->where('nip', '!=', $item->NIP)->exists()) {
            $email = str_replace('@', $counter . '@', $baseEmail);
            $counter++;
        }

        $data = [
            'name' => $item->Nama,
            'email' => $item->email ?? $email,
            'unit_id' => $unitId,
            'status' => 'active',
            'tempat_lahir' => $item->{'Tempat Lahir'},
            'alamat' => $item->Alamat,
            'jenis_kelamin' => $item->{'L/P'},
            'agama' => $item->Agama,
            'golongan' => $item->Gol,
            'eselon' => $item->Eselon,
            'grade' => $item->GRADE,
            'no_hp' => $item->{'No. HP'},
            'telepon' => $item->Telepon,
            'npwp' => $item->NPWP,
            'pendidikan_terakhir' => $item->{'Pendidikan Terakhir'},
            'pendidikan_jurusan' => $item->{'Unnamed: 17'},
            'pendidikan_universitas' => $item->{'Unnamed: 18'},
            'jenis_jabatan' => $item->{'Jenis Jabatan'},
        ];

        // Add password only if creating a new user
        if (!isset($this->userCache[$item->NIP])) {
            $data['password'] = Hash::make('password');
        }

        // Convert date formats
        $dateFields = [
            'tgl_lahir' => 'Tgl. Lahir',
            'tmt_eselon' => 'TMT ESELON',
            'tmt_cpns' => 'TMT CPNS',
            'tmt_pns' => 'TMT PNS'
        ];

        foreach ($dateFields as $dbKey => $jsonKey) {
            if (!empty($item->{$jsonKey})) {
                try {
                    $data[$dbKey] = Carbon::createFromFormat('d-m-Y', $item->{$jsonKey})->format('Y-m-d');
                } catch (\Exception $e) {
                    $this->logInfo("Could not parse date '{$item->{$jsonKey}}' for user NIP {$item->NIP}. Setting to null.");
                    $data[$dbKey] = null;
                }
            }
        }

        return $data;
    }

    private function getOrCreateJabatan(object $item, int $unitId, int $userId, string $role): Jabatan
    {
        $jabatanName = $item->Jabatan;
        if (empty($jabatanName)) {
            $this->logError("Skipping Jabatan creation for User ID: {$userId} due to empty Jabatan name.");
            // Create a default placeholder Jabatan to avoid crashes downstream
            return Jabatan::firstOrCreate(
                ['user_id' => $userId],
                ['name' => 'Jabatan Belum Diatur', 'unit_id' => $unitId]
            );
        }

        // First, check if this specific user is already in a Jabatan with this name/unit.
        $existingJabatan = Jabatan::where('name', 'like', $jabatanName)
                                ->where('unit_id', $unitId)
                                ->where('user_id', $userId)
                                ->first();

        if ($existingJabatan) {
            return $existingJabatan;
        }

        // The user is not in this Jabatan. Let's see if there's a vacant one.
        $vacantJabatan = Jabatan::where('name', 'like', $jabatanName)
                               ->where('unit_id', $unitId)
                               ->whereNull('user_id')
                               ->first();

        if ($vacantJabatan) {
            $vacantJabatan->user_id = $userId;
            $vacantJabatan->save();
            return $vacantJabatan;
        }

        // No vacant slots found. We must create a new Jabatan slot for this user.
        return Jabatan::create([
            'name'      => $jabatanName,
            'unit_id'   => $unitId,
            'user_id'   => $userId,
        ]);
    }

    private function isStructuralHead(User $user): bool
    {
        // This check is now delegated to the User model's hasRole method.
        return $user->hasRole(['Menteri', 'Eselon I', 'Eselon II', 'Koordinator', 'Sub Koordinator']);
    }

    private function updateSupervisorForAllUsers(): void
    {
        $this->logInfo('Updating supervisor relationships...');
        // Eager load units to reduce queries and find all unit heads first.
        $users = User::with('unit.parentUnit')->get();
        $unitHeads = Unit::whereNotNull('kepala_unit_id')->pluck('kepala_unit_id', 'id')->toArray();
        $updates = [];

        foreach ($users as $user) {
            if (!$user->unit) continue;

            $supervisorId = null;

            // Find the head of the user's own unit
            if (isset($unitHeads[$user->unit->id]) && $unitHeads[$user->unit->id] !== $user->id) {
                // If there is a head of this unit, and it's not the user themselves, that's the supervisor.
                $supervisorId = $unitHeads[$user->unit->id];
            } elseif ($user->unit->parentUnit && isset($unitHeads[$user->unit->parentUnit->id])) {
                // Otherwise, the supervisor is the head of the parent unit.
                $supervisorId = $unitHeads[$user->unit->parentUnit->id];
            }

            if ($user->atasan_id !== $supervisorId) {
                $updates[$user->id] = $supervisorId;
            }
        }

        if (!empty($updates)) {
            foreach ($updates as $userId => $atasanId) {
                User::where('id', $userId)->update(['atasan_id' => $atasanId]);
            }
        }
        $this->logInfo('Supervisor relationships updated.');
    }

    private function logInfo(string $message): void
    {
        if ($this->command) {
            $this->command->info($message);
        } else {
            Log::info($message);
        }
    }

    private function logError(string $message): void
    {
        if ($this->command) {
            $this->command->error($message);
        } else {
            Log::error($message);
        }
    }
}