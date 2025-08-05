<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WeeklyWorkloadController extends Controller
{
    // Standar jam kerja per minggu
    const STANDARD_WEEKLY_HOURS = 37.5;

    public function index(Request $request)
    {
        $manager = Auth::user();
        $search = $request->input('search');

        // Dapatkan query dasar untuk bawahan, tergantung pada peran manajer
        if ($manager->role === User::ROLE_SUPERADMIN || $manager->role === User::ROLE_MENTERI) {
            $subordinatesQuery = User::where('id', '!=', $manager->id);
        } else {
            $subordinateUnitIds = $manager->unit ? $manager->unit->getAllSubordinateUnitIds() : [];
            $subordinatesQuery = User::whereIn('unit_id', $subordinateUnitIds)->where('id', '!=', $manager->id);
        }

        // Terapkan filter pencarian nama jika ada
        if ($search) {
            $subordinatesQuery->where('name', 'like', '%' . $search . '%');
        }

        // Eager load jumlah jam tugas untuk menghindari N+1 query problem
        // Hasilnya akan tersedia sebagai atribut 'total_assigned_hours' pada setiap model User.
        $subordinatesQuery->withSum(['tasks as total_assigned_hours' => function ($query) {
            $query->where('status', '!=', 'Selesai');
        }], 'estimated_hours');

        // Ambil data dengan paginasi
        $teamMembers = $subordinatesQuery->paginate(20)->withQueryString();

        return view('weekly_workload.index', [
            'teamMembers' => $teamMembers, // Kirim paginator ke view
            'standardHours' => self::STANDARD_WEEKLY_HOURS,
            'search' => $search
        ]);
    }
}