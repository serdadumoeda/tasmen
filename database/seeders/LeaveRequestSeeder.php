<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\LeaveType;
use App\Models\LeaveRequest;
use App\Services\LeaveDurationService;
use Carbon\Carbon;

class LeaveRequestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Seeding dummy leave requests...');

        $stafRole = \App\Models\Role::where('name', 'staf')->first();
        if (!$stafRole) {
            $this->command->warn('Staf role not found. Skipping leave request seeding.');
            return;
        }

        $users = User::where('role_id', $stafRole->id)->whereNotNull('atasan_id')->get();
        if ($users->isEmpty()) {
            $this->command->warn('No staff users with supervisors found. Skipping leave request seeding.');
            return;
        }

        $leaveTypes = LeaveType::all();
        if ($leaveTypes->isEmpty()) {
            $this->command->warn('No leave types found. Please run LeaveTypesSeeder first. Skipping leave request seeding.');
            return;
        }

        $statuses = ['pending', 'approved', 'rejected', 'approved_by_supervisor'];

        for ($i = 0; $i < 30; $i++) {
            $user = $users->random();
            $leaveType = $leaveTypes->random();

            $startDate = Carbon::now()->subDays(rand(-30, 30)); // Random start date around today
            $endDate = $startDate->copy()->addDays(rand(0, 10));

            $duration = LeaveDurationService::calculate($startDate, $endDate);
            // Ensure duration is at least 1 day for this seeder
            if ($duration <= 0) {
                $endDate->addDay();
                $duration = LeaveDurationService::calculate($startDate, $endDate);
                 if ($duration <= 0) continue; // Skip if still invalid
            }

            $status = $statuses[array_rand($statuses)];
            $approverId = null;
            $rejectionReason = null;

            if ($status !== 'pending') {
                $approverId = $user->atasan_id;
            }
            if ($status === 'rejected') {
                $rejectionReason = 'Alasan penolakan otomatis dari seeder.';
            }

            LeaveRequest::create([
                'user_id' => $user->id,
                'leave_type_id' => $leaveType->id,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'duration_days' => $duration,
                'reason' => 'Permintaan cuti otomatis dari seeder.',
                'address_during_leave' => 'Jl. Seeder No. ' . ($i + 1),
                'status' => $status,
                'current_approver_id' => ($status === 'pending' || $status === 'approved_by_supervisor') ? $user->atasan_id : null,
                'rejection_reason' => $rejectionReason,
            ]);
        }

        $this->command->info('Dummy leave requests have been seeded successfully.');
    }
}
