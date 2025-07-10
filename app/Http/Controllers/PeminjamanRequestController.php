<?php

namespace App\Http\Controllers;

use App\Models\PeminjamanRequest;
use App\Models\Project;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;
use Carbon\Carbon; 


class PeminjamanRequestController extends Controller
{
    // Menampilkan daftar permintaan yang perlu disetujui oleh user yang login
    public function index()
    {
        $pendingRequests = PeminjamanRequest::where('approver_id', Auth::id())
            ->where('status', 'pending')
            ->with(['project', 'requester', 'requestedUser'])
            ->latest()
            ->get();

        return view('peminjaman_requests.index', compact('pendingRequests'));
    }

    // Menyimpan permintaan peminjaman baru
    public function store(Request $request)
    {
        $request->validate([
            'project_id' => 'required|exists:projects,id',
            'requested_user_id' => 'required|exists:users,id',
            'message' => 'nullable|string|max:1000',
        ]);

        $requestedUser = User::findOrFail($request->requested_user_id);

        // Pastikan user punya atasan
        if (!$requestedUser->parent_id) {
            return response()->json(['success' => false, 'message' => 'Anggota yang dipilih tidak memiliki atasan untuk dimintai persetujuan.'], 422);
        }

        $peminjamanRequest = PeminjamanRequest::create([
            'project_id' => $request->project_id,
            'requested_user_id' => $requestedUser->id,
            'requester_id' => Auth::id(),
            'approver_id' => $requestedUser->parent_id,
            'message' => $request->message,
            'due_date' => Carbon::now()->addWeekday(),
        ]);

        // Kirim notifikasi ke approver (akan kita aktifkan nanti)
        // $approver = User::find($requestedUser->parent_id);
        // Notification::send($approver, new PeminjamanRequested($peminjamanRequest));

        return response()->json(['success' => true, 'message' => 'Permintaan peminjaman anggota telah berhasil dikirim.']);
    }

    // Menyetujui permintaan
    public function approve(PeminjamanRequest $peminjamanRequest)
    {
        // Pastikan yang menyetujui adalah orang yang tepat
        if ($peminjamanRequest->approver_id !== Auth::id()) {
            abort(403, 'Anda tidak berwenang melakukan tindakan ini.');
        }

        // Tambahkan user ke proyek
        $project = Project::find($peminjamanRequest->project_id);
        $project->members()->syncWithoutDetaching([$peminjamanRequest->requested_user_id]);

        // Update status permintaan
        $peminjamanRequest->update(['status' => 'approved']);
        
        // Kirim notifikasi hasil ke requester (akan kita aktifkan nanti)
        // Notification::send($peminjamanRequest->requester, new PeminjamanResult($peminjamanRequest));

        return redirect()->route('peminjaman-requests.index')->with('success', 'Permintaan telah disetujui.');
    }

    // Menolak permintaan
    public function reject(Request $request, PeminjamanRequest $peminjamanRequest)
    {
        if ($peminjamanRequest->approver_id !== Auth::id()) {
            abort(403, 'Anda tidak berwenang melakukan tindakan ini.');
        }

        $request->validate(['rejection_reason' => 'required|string|max:1000']);

        $peminjamanRequest->update([
            'status' => 'rejected',
            'rejection_reason' => $request->rejection_reason,
        ]);
        
        // Kirim notifikasi hasil ke requester (akan kita aktifkan nanti)
        // Notification::send($peminjamanRequest->requester, new PeminjamanResult($peminjamanRequest));

        return redirect()->route('peminjaman-requests.index')->with('success', 'Permintaan telah ditolak.');
    }
}