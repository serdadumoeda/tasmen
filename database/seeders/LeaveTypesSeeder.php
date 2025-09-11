<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\LeaveType;

class LeaveTypesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $leaveTypes = [
            ['name' => 'Cuti Tahunan', 'default_days' => 12, 'is_annual' => true, 'requires_attachment' => false],
            ['name' => 'Cuti Besar', 'default_days' => 90, 'is_annual' => false, 'requires_attachment' => false],
            ['name' => 'Cuti Sakit', 'default_days' => null, 'is_annual' => false, 'requires_attachment' => true],
            ['name' => 'Cuti Melahirkan', 'default_days' => 90, 'is_annual' => false, 'requires_attachment' => true],
            ['name' => 'Cuti Alasan Penting', 'default_days' => null, 'is_annual' => false, 'requires_attachment' => true],
            ['name' => 'Cuti di Luar Tanggungan Negara', 'default_days' => null, 'is_annual' => false, 'requires_attachment' => false],
        ];

        foreach ($leaveTypes as $type) {
            LeaveType::updateOrCreate(
                ['name' => $type['name']],
                [
                    'default_days' => $type['default_days'],
                    'is_annual' => $type['is_annual'],
                    'requires_attachment' => $type['requires_attachment'],
                ]
            );
        }
    }
}
