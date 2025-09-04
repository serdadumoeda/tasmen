<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Task;
use App\Models\TaskStatus;
use PDF; // Pastikan Anda sudah install library seperti barryvdh/laravel-dompdf

class LaporanKinerjaController extends Controller
{
    public function index()
    {
        return view('laporan_kinerja.index');
    }

    public function generate(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        $user = Auth::user();
        $startDate = $request->start_date;
        $endDate = $request->end_date;

        // Ambil ID status "Completed"
        $completedStatusId = TaskStatus::where('name', 'Completed')->firstOrFail()->id;

        // Ambil semua tugas (termasuk ad-hoc) yang diselesaikan oleh user dalam rentang waktu
        $completedTasks = Task::where('status_id', $completedStatusId)
            ->whereHas('users', function ($query) use ($user) {
                $query->where('users.id', $user->id);
            })
            ->whereDate('updated_at', '>=', $startDate) // Asumsi tgl selesai adalah updated_at
            ->whereDate('updated_at', '<=', $endDate)
            ->with('project') // Eager load project untuk info tambahan
            ->orderBy('updated_at', 'asc')
            ->get();

        $data = [
            'user' => $user,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'tasks' => $completedTasks,
        ];

        // Generate PDF
        $pdf = PDF::loadView('pdf.laporan_kinerja', $data);
        return $pdf->stream('LKH-' . $user->name . '-' . $startDate . '.pdf');
    }
}
