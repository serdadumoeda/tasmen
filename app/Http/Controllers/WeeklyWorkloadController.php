<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WeeklyWorkloadController extends Controller
{
    // Standar jam kerja per minggu
    const STANDARD_WEEKLY_HOURS = 37.5;

    public function index()
    {
        $manager = Auth::user();
        // Ambil semua bawahan dari manajer yang sedang login
        $teamMembers = $manager->getAllSubordinates();

        $workloadData = $teamMembers->map(function ($member) {
            // Hitung total jam dari tugas yang belum selesai
            $totalAssignedHours = $member->tasks()
                ->where('status', '!=', 'Selesai')
                ->sum('estimated_hours');

            // Hitung persentase beban kerja
            $workloadPercentage = (self::STANDARD_WEEKLY_HOURS > 0)
                ? ($totalAssignedHours / self::STANDARD_WEEKLY_HOURS) * 100
                : 0;

            return [
                'user' => $member,
                'assigned_hours' => $totalAssignedHours,
                'workload_percentage' => round($workloadPercentage)
            ];
        });

        return view('weekly_workload.index', [
            'workloadData' => $workloadData,
            'standardHours' => self::STANDARD_WEEKLY_HOURS
        ]);
    }
}