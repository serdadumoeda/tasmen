<?php

namespace App\Services;

use App\Models\User;
use App\Models\Unit;
use App\Models\Jabatan;
use App\Models\Role;
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
        // Cache roles on construction
        $this->roleCache = Role::pluck('id', 'name')->toArray();
    }

    public function processData(array $data): void
    {
        $this->logInfo('Starting data processing...');
        if ($this->command) {
            $this->command->getOutput()->progressStart(count($data));
        }

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
        
        $this->updateSupervisorForAllUsers();
        Unit::rebuildPaths();

        if ($this->command) {
            $this->command->getOutput()->progressFinish();
        }
        $this->logInfo('Data processing finished.');
    }

    private function processItem(object $item): void
    {
        $unit = $this->getOrCreateUnit($item);
        if (!$unit) {
            $this->logInfo('Skipping item due to missing unit information for NIP: ' . $item->NIP);
            return;
        }

        $roleName = $this->determineRoleName($item, $unit);
        $roleId = $this->roleCache[$roleName] ?? null;

        if (!$roleId) {
            $this->logError("Could not find role ID for role name '{$roleName}'. Skipping user.");
            return;
        }

        $userData = $this->prepareUserData($item, $unit->id, $roleId);

        $user = User::updateOrCreate(['nip' => $item->NIP], $userData);

        $this->getOrCreateJabatan($item, $unit->id, $user->id);

        if ($this->isStructuralHead($roleName)) {
            $unit->kepala_unit_id = $user->id;
            $unit->save();
        }
    }

    private function getOrCreateUnit(object $item): ?Unit
    {
        $unitFields = [
            'Unit Kerja Eselon I', 'Unit Kerja Eselon II',
            'Unit Kerja Koordinator', 'Unit Kerja Sub Koordinator'
        ];
        $parentUnitId = null;
        $lastUnit = null;

        foreach ($unitFields as $field) {
            $unitName = trim($item->{$field} ?? '');
            if (empty($unitName)) continue;

            $cacheKey = $parentUnitId . '_' . $unitName;
            if (isset($this->unitCache[$cacheKey])) {
                $unit = Unit::find($this->unitCache[$cacheKey]);
            } else {
                $unit = Unit::firstOrCreate(
                    ['name' => $unitName, 'parent_unit_id' => $parentUnitId]
                );
                $this->unitCache[$cacheKey] = $unit->id;
            }
            $parentUnitId = $unit->id;
            $lastUnit = $unit;
        }
        return $lastUnit;
    }

    private function determineRoleName(object $item, Unit $unit): string
    {
        if (!empty($item->Eselon)) {
            $roleName = match ($item->Eselon) {
                '1-A' => 'eselon_i',
                '2-A' => 'eselon_ii',
                '3-A' => 'koordinator',
                '4-A' => 'sub_koordinator',
                default => null,
            };
            if ($roleName) {
                return $roleName;
            }
        }

        $depth = $unit->ancestors()->count();
        return match ($depth) {
            1 => 'eselon_i',
            2 => 'eselon_ii',
            3 => 'koordinator',
            4 => 'sub_koordinator',
            default => 'staf',
        };
    }

    private function prepareUserData(object $item, int $unitId, int $roleId): array
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
            'role_id' => $roleId,
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

        if (!isset($this->userCache[$item->NIP])) {
            $data['password'] = Hash::make('password');
        }

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

    private function getOrCreateJabatan(object $item, int $unitId, int $userId): Jabatan
    {
        $jabatanName = $item->Jabatan;
        if (empty($jabatanName)) {
            $this->logError("Skipping Jabatan creation for User ID: {$userId} due to empty Jabatan name.");
            return Jabatan::firstOrCreate(
                ['user_id' => $userId],
                ['name' => 'Jabatan Belum Diatur', 'unit_id' => $unitId]
            );
        }

        $existingJabatan = Jabatan::where('name', 'like', $jabatanName)
                                ->where('unit_id', $unitId)
                                ->where('user_id', $userId)
                                ->first();

        if ($existingJabatan) return $existingJabatan;

        $vacantJabatan = Jabatan::where('name', 'like', $jabatanName)
                               ->where('unit_id', $unitId)
                               ->whereNull('user_id')
                               ->first();

        if ($vacantJabatan) {
            $vacantJabatan->user_id = $userId;
            $vacantJabatan->save();
            return $vacantJabatan;
        }

        return Jabatan::create([
            'name'      => $jabatanName,
            'unit_id'   => $unitId,
            'user_id'   => $userId,
        ]);
    }

    private function isStructuralHead(string $roleName): bool
    {
        return in_array($roleName, [
            'menteri', 'eselon_i', 'eselon_ii', 'koordinator', 'sub_koordinator'
        ]);
    }

    private function updateSupervisorForAllUsers(): void
    {
        $this->logInfo('Updating supervisor relationships...');
        $users = User::with('unit.parentUnit')->get();
        $unitHeads = Unit::whereNotNull('kepala_unit_id')->pluck('kepala_unit_id', 'id')->toArray();
        $updates = [];

        foreach ($users as $user) {
            if (!$user->unit) continue;

            $supervisorId = null;
            if (isset($unitHeads[$user->unit->id]) && $unitHeads[$user->unit->id] !== $user->id) {
                $supervisorId = $unitHeads[$user->unit->id];
            } elseif ($user->unit->parentUnit && isset($unitHeads[$user->unit->parentUnit->id])) {
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