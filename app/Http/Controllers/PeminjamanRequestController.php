<?php

namespace App\Http\Controllers;

use App\Models\PeminjamanRequest;
use App\Models\Project;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Scopes\HierarchicalScope;
use App\Notifications\PeminjamanRequested;
use App\Notifications\PeminjamanApproved;
use App\Notifications\PeminjamanRejected;

class PeminjamanRequestController extends Controller
{
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'project_id' => 'required|exists:projects,id',
            'requested_user_id' => 'required|exists:users,id',
            'message' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak valid.',
                'errors' => $validator->errors()
            ], 422);
        }

        $requestedUser = User::findOrFail($request->requested_user_id);

        // --- AWAL LOGIKA BARU YANG DIPERBAIKI ---
        $approver = $this->findCoordinator($requestedUser);

        if (!$approver) {
            return response()->json(['success' => false, 'message' => 'Koordinator untuk anggota ini tidak dapat ditemukan. Tidak bisa melanjutkan permintaan.'], 422);
        }
        // --- AKHIR LOGIKA BARU ---

        $peminjamanRequest = PeminjamanRequest::create($request->all() + [
            'requester_id' => Auth::id(), 
            'approver_id' => $approver->id, // Gunakan approver yang sudah ditemukan
            'due_date' => now()->addWeekday(),
        ]);
        
        Notification::send($approver, new PeminjamanRequested($peminjamanRequest));

        return response()->json(['success' => true, 'message' => 'Permintaan peminjaman anggota telah berhasil dikirim.']);
    }

    /**
     * Fungsi baru untuk mencari Koordinator di atas seorang user.
     * Ia akan naik dalam hierarki sampai menemukan user dengan peran 'Koordinator'.
     */
    private function findCoordinator(User $user): ?User
    {
        $currentUser = $user;
        while ($currentUser->parent) {
            $currentUser = $currentUser->parent;
            if ($currentUser->role === 'Koordinator') {
                return $currentUser; // Ditemukan, kembalikan user Koordinator
            }
            // Jika mencapai Eselon II atau lebih tinggi tanpa menemukan Koordinator, berhenti.
            if (in_array($currentUser->role, ['Eselon II', 'Eselon I'])) {
                return null;
            }
        }
        return null; // Tidak ditemukan Koordinator dalam hierarki ke atas
    }
    
    // ... (Sisa method tidak berubah)
    public function approve(PeminjamanRequest $peminjamanRequest)
    {
        if ($peminjamanRequest->approver_id !== Auth::id()) {
            abort(403, 'Anda tidak berwenang melakukan tindakan ini.');
        }

        try {
            $project = Project::withoutGlobalScope(HierarchicalScope::class)->findOrFail($peminjamanRequest->project_id);
            $project->members()->syncWithoutDetaching([$peminjamanRequest->requested_user_id]);
            $peminjamanRequest->update(['status' => 'approved']);

            $requester = $peminjamanRequest->requester;
            if ($requester) {
                Notification::send($requester, new PeminjamanApproved($peminjamanRequest));
            }

            return redirect()->route('peminjaman-requests.my-requests')->with('success', 'Permintaan telah disetujui.');
        } catch (ModelNotFoundException $e) {
            return redirect()->route('peminjaman-requests.my-requests')->with('error', 'Gagal menyetujui: Proyek terkait tidak ditemukan.');
        }
    }

    public function reject(Request $request, PeminjamanRequest $peminjamanRequest)
    {
        if ($peminjamanRequest->approver_id !== Auth::id()) {
            abort(403, 'Anda tidak berwenang melakukan tindakan ini.');
        }

        $request->validate(['rejection_reason' => 'required|string|max:1000']);
        $peminjamanRequest->update($request->only('rejection_reason') + ['status' => 'rejected']);
        
        $requester = $peminjamanRequest->requester;
        if ($requester) {
            Notification::send($requester, new PeminjamanRejected($peminjamanRequest));
        }

        return redirect()->route('peminjaman-requests.my-requests')->with('success', 'Permintaan telah ditolak.');
    }

    public function index()
    {
        return redirect()->route('peminjaman-requests.my-requests');
    }

    public function myRequests()
    {
        $userId = Auth::id();

        $mySentRequests = PeminjamanRequest::where('requester_id', $userId)
            ->with(['project', 'requestedUser', 'approver'])
            ->latest()
            ->get();
            
        $myPendingApprovals = PeminjamanRequest::where('approver_id', $userId)
            ->where('status', 'pending')
            ->with(['project' => fn($query) => $query->withoutGlobalScope(HierarchicalScope::class), 'requester', 'requestedUser', 'approver'])
            ->latest()
            ->get();

        return view('peminjaman_requests.my_requests', compact('mySentRequests', 'myPendingApprovals'));
    }
}