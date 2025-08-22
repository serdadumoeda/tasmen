<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\LeaveBalance;
use App\Models\LeaveType;
use Carbon\Carbon;

class GenerateLeaveBalances extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:generate-leave-balances';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate annual leave balances for all users for the new year, including carry-over logic.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting to generate leave balances for the new year...');

        $currentYear = now()->year;
        $previousYear = $currentYear - 1;

        $annualLeaveType = LeaveType::where('name', 'Cuti Tahunan')->first();
        if (!$annualLeaveType) {
            $this->error('"Cuti Tahunan" leave type not found. Please seed the leave types first.');
            return 1;
        }
        $defaultAnnualLeave = $annualLeaveType->default_days ?? 12;
        // ASN rule: max 6 days carry-over, valid for 1 year only.
        $maxCarryOver = 6;

        $users = User::where('status', 'active')->get();

        foreach ($users as $user) {
            // Check if a balance for the current year already exists
            $existingBalance = LeaveBalance::where('user_id', $user->id)
                ->where('year', $currentYear)
                ->exists();

            if ($existingBalance) {
                $this->line("Balance for {$user->name} for year {$currentYear} already exists. Skipping.");
                continue;
            }

            // Calculate carry-over from the previous year
            $carriedOverDays = 0;
            $previousBalance = LeaveBalance::where('user_id', $user->id)
                ->where('year', $previousYear)
                ->first();

            if ($previousBalance) {
                $remainingLastYear = ($previousBalance->total_days + $previousBalance->carried_over_days) - $previousBalance->days_taken;
                // Apply carry-over rules: max 6 days can be carried over.
                $carriedOverDays = min($maxCarryOver, max(0, $remainingLastYear));
            }

            // Create the new balance record
            LeaveBalance::create([
                'user_id' => $user->id,
                'year' => $currentYear,
                'total_days' => $defaultAnnualLeave,
                'carried_over_days' => $carriedOverDays,
                'days_taken' => 0,
            ]);

            $this->info("Generated balance for {$user->name}. Annual: {$defaultAnnualLeave}, Carried Over: {$carriedOverDays}");
        }

        $this->info('Successfully generated leave balances for all active users.');
        return 0;
    }
}
