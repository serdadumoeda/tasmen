<?php

namespace App\Http\Controllers;

use App\Models\PeminjamanRequest;
use App\Models\Project;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Scopes\HierarchicalScope;

class PeminjamanRequestController extends Controller
{
    /**
     * Arahkan halaman index lama ke halaman status yang baru.
     */
    public function index()
    {
        return redirect()->route('peminjaman-requests.my-requests');
    }

    public function myRequests()
    {
        $userId = Auth::id();

        // 1. Permintaan yang SAYA AJUKAN ke atasan lain
        $mySentRequests = PeminjamanRequest::where('requester_id', $userId)
            ->with(['project', 'requestedUser', 'approver'])
            ->latest()
            ->get();

        // 2. Permintaan yang perlu SAYA SETUJUI (dari atasan lain untuk tim saya)
        $myPendingApprovals = PeminjamanRequest::where('approver_id', $userId)
            ->where('status', 'pending')
            ->with(['project', 'requester', 'requestedUser'])
            ->latest()
            ->get();

        return view('peminjaman_requests.my_requests', compact('mySentRequests', 'myPendingApprovals'));
    }

    /**
     * Menyimpan permintaan peminjaman baru
     */
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
            'due_date' => \Carbon\Carbon::now()->addWeekday(),
        ]);

        return response()->json(['success' => true, 'message' => 'Permintaan peminjaman anggota telah berhasil dikirim.']);
    }
    
    public function approve(PeminjamanRequest $peminjamanRequest)
    {
        if ($peminjamanRequest->approver_id !== Auth::id()) {
            abort(403, 'Anda tidak berwenang melakukan tindakan ini.');
        }

        try {
            // --- PERBAIKAN UTAMA DI SINI ---
            // Cari proyek berdasarkan ID, tapi abaikan HierarchicalScope untuk sementara.
            $project = Project::withoutGlobalScope(HierarchicalScope::class)
                              ->findOrFail($peminjamanRequest->project_id);
            // --- AKHIR PERBAIKAN ---

            // Tambahkan user ke proyek
            $project->members()->syncWithoutDetaching([$peminjamanRequest->requested_user_id]);

            // Update status permintaan
            $peminjamanRequest->update(['status' => 'approved']);

            return redirect()->route('peminjaman-requests.my-requests')->with('success', 'Permintaan telah disetujui dan anggota telah ditambahkan ke proyek.');

        } catch (ModelNotFoundException $e) {
            // Blok catch ini sekarang hanya akan berjalan jika proyeknya BENAR-BENAR sudah dihapus.
            $peminjamanRequest->update([
                'status' => 'rejected',
                'rejection_reason' => 'Ditolak otomatis karena proyek terkait telah dihapus.',
            ]);
            
            return redirect()->route('peminjaman-requests.my-requests')->with('error', 'Gagal menyetujui: Proyek yang terkait dengan permintaan ini tidak dapat ditemukan.');
        }
    }

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
        
        return redirect()->route('peminjaman-requests.my-requests')->with('success', 'Permintaan telah ditolak.');
    }
}