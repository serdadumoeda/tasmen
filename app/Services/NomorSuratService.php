<?php

namespace App\Services;

use App\Models\KlasifikasiSurat;
use App\Models\Surat;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class NomorSuratService
{
    /**
     * Generate a new, unique letter number.
     *
     * The format is: [Kode Klasifikasi]/[Nomor Urut]/[Kode Unit Kerja]/[Bulan Romawi]/[Tahun]
     * Example: HK.01.01/123/Pusdatin/IX/2025
     *
     * @param KlasifikasiSurat $klasifikasi The classification chosen for the letter.
     * @param User $pembuat The user creating the letter, used to find the work unit.
     * @return string The generated letter number.
     * @throws \Exception If the user does not have a unit with a code.
     */
    public function generate(KlasifikasiSurat $klasifikasi, User $pembuat): string
    {
        return DB::transaction(function () use ($klasifikasi, $pembuat) {
            $now = Carbon::now();
            $year = $now->year;
            $month = $now->month;

            // 1. Get components
            $classificationCode = $klasifikasi->kode;
            $unitCode = $pembuat->unit->kode ?? null;

            if (!$unitCode) {
                throw new \Exception("User's unit or unit code not found.");
            }

            $romanMonth = $this->toRoman($month);

            // 2. Find the last sequence number for this combination
            // We lock the table to prevent race conditions.
            $lastSurat = Surat::whereYear('created_at', $year)
                ->whereMonth('created_at', $month)
                ->whereHas('pembuat.unit', function ($query) use ($unitCode) {
                    $query->where('kode', $unitCode);
                })
                ->where('klasifikasi_id', $klasifikasi->id)
                ->lockForUpdate()
                ->latest('id')
                ->first();

            $nextSequence = 1;
            if ($lastSurat && $lastSurat->nomor_surat) {
                // Extract the sequence number from the last letter number
                $parts = explode('/', $lastSurat->nomor_surat);
                if (isset($parts[1]) && is_numeric($parts[1])) {
                    $nextSequence = (int)$parts[1] + 1;
                }
            }

            // 3. Format the new letter number
            $nomorUrut = str_pad($nextSequence, 3, '0', STR_PAD_LEFT); // e.g., 001, 012, 123

            return "{$classificationCode}/{$nomorUrut}/{$unitCode}/{$romanMonth}/{$year}";
        });
    }

    /**
     * Convert an integer to its Roman numeral representation.
     *
     * @param int $integer
     * @return string
     */
    private function toRoman(int $integer): string
    {
        $map = [
            'M' => 1000, 'CM' => 900, 'D' => 500, 'CD' => 400,
            'C' => 100, 'XC' => 90, 'L' => 50, 'XL' => 40,
            'X' => 10, 'IX' => 9, 'V' => 5, 'IV' => 4, 'I' => 1
        ];
        $returnValue = '';
        while ($integer > 0) {
            foreach ($map as $roman => $int) {
                if ($integer >= $int) {
                    $integer -= $int;
                    $returnValue .= $roman;
                    break;
                }
            }
        }
        return $returnValue;
    }
}
