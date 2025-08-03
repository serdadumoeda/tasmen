<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\PerformanceCalculatorService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CalculatePerformanceScores extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:calculate-performance-scores';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Calculate and store performance scores for all users';

    /**
     * The service that handles the calculation logic.
     *
     * @var PerformanceCalculatorService
     */
    protected $calculator;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(PerformanceCalculatorService $calculator)
    {
        parent::__construct();
        $this->calculator = $calculator;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting performance score calculation...');
        Log::info('Scheduled task: Starting performance score calculation.');

        // Panggil service untuk menjalankan seluruh siklus perhitungan.
        $this->calculator->calculateForAllUsers();

        Log::info('Scheduled task: Finished performance score calculation.');
        $this->info('Performance score calculation completed successfully.');
        return 0;
    }
}
