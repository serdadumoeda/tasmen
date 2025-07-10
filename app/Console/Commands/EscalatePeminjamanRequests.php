<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\PeminjamanRequest;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
// use Illuminate\Support\Facades\Notification;
// use App\Notifications\PeminjamanEscalated; // Notifikasi akan dibuat nanti

class EscalatePeminjamanRequests extends Command
{
    /**
     * Nama dan signature dari console command.
     *
     * @var string
     */
    protected $signature = 'peminjaman:escalate';

    /**
     * Deskripsi dari console command.
     *
     * @var string
     */
    protected $description = 'Mencari permintaan peminjaman yang terlambat dan meneruskannya ke atasan berikutnya.';

    /**
     * Jalankan console command.
     */
    public function handle()
    {
        $this->info('Mulai proses eskalasi permintaan peminjaman...');
        Log::info('Scheduler Eskalasi Peminjaman: Memulai pengecekan.');

        // Cari semua permintaan yang statusnya 'pending' dan sudah melewati batas waktu (due_date)
        $overdueRequests = PeminjamanRequest::where('status', 'pending')
            ->where('due_date', '<', Carbon::now())
            ->get();

        if ($overdueRequests->isEmpty()) {
            $this->info('Tidak ada permintaan yang terlambat ditemukan.');
            Log::info('Scheduler Eskalasi Peminjaman: Tidak ada permintaan terlambat.');
            return;
        }

        $this->info("Ditemukan {$overdueRequests->count()} permintaan yang terlambat. Memproses...");

        foreach ($overdueRequests as $request) {
            $currentApprover = User::find($request->approver_id);

            // Cari atasan dari approver saat ini
            $nextApprover = $currentApprover->parent;

            // Jika tidak ada lagi atasan (sudah di puncak hierarki), maka lewati
            if (!$nextApprover) {
                $this->warn("Permintaan #{$request->id} untuk user {$request->requestedUser->name} sudah berada di puncak hierarki. Tidak bisa dieskalasi lebih lanjut.");
                Log::warning("Scheduler Eskalasi Peminjaman: Permintaan #{$request->id} tidak bisa dieskalasi lagi.");
                // Update due_date agar tidak dicek terus-menerus
                $request->update(['due_date' => null]);
                continue;
            }

            // Lakukan update pada permintaan
            $request->update([
                'approver_id'       => $nextApprover->id,
                'escalation_level'  => $request->escalation_level + 1,
                'due_date'          => Carbon::now()->addWeekday(), // Atur batas waktu baru
            ]);

            $this->info("Permintaan #{$request->id} telah dieskalasi dari {$currentApprover->name} ke {$nextApprover->name}.");
            Log::info("Scheduler Eskalasi Peminjaman: Permintaan #{$request->id} dieskalasi ke {$nextApprover->name} (ID: {$nextApprover->id}).");
            
            // Kirim notifikasi ke approver yang baru (akan diaktifkan nanti)
            // Notification::send($nextApprover, new PeminjamanEscalated($request));
        }

        $this->info('Proses eskalasi selesai.');
        Log::info('Scheduler Eskalasi Peminjaman: Proses selesai.');
    }
}