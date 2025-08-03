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
    public function index()
    {
        return redirect()->route('peminjaman-requests.my-requests');
    }

    public function myRequests()
    {
        $userId = Auth::id();

        // --- AWAL PERBAIKAN ---

        // 1. Ambil permintaan yang perlu disetujui (tidak perlu pagination)
        $myPendingApprovals = PeminjamanRequest::where('approver_id', $userId)
            ->where('status', 'pending')
            ->with(['project' => fn($query) => $query->withoutGlobalScope(HierarchicalScope::class), 'requester', 'requestedUser'])
            ->latest()
            ->get();

        // 2. Ambil riwayat permintaan yang SAYA ajukan (dengan pagination)
        $mySentRequests = PeminjamanRequest::where('requester_id', $userId)
            ->with(['project', 'requestedUser', 'approver'])
            ->latest()
            ->paginate(5, ['*'], 'sent_page'); // 5 item per halaman, nama paginator: sent_page

        // 3. Ambil riwayat persetujuan yang TELAH SAYA PROSES (dengan pagination)
        $approvalHistory = PeminjamanRequest::where('approver_id', $userId)
            ->whereIn('status', ['approved', 'rejected']) // Hanya yang sudah disetujui/ditolak
            ->with(['project', 'requester', 'requestedUser'])
            ->latest()
            ->paginate(5, ['*'], 'history_page'); // 5 item per halaman, nama paginator: history_page

        return view('peminjaman_requests.my_requests', compact(
            'myPendingApprovals',
            'mySentRequests',
            'approvalHistory'
        ));
        // --- AKHIR PERBAIKAN ---
    }

    /**
     * Method baru untuk menghapus riwayat permintaan/persetujuan.
     */
    public function destroy(PeminjamanRequest $peminjamanRequest)
    {
        // Hanya peminta atau approver yang boleh menghapus riwayat
        if (Auth::id() !== $peminjamanRequest->requester_id && Auth::id() !== $peminjamanRequest->approver_id) {
            abort(403, 'Anda tidak berwenang melakukan tindakan ini.');
        }

        $peminjamanRequest->delete();

        return redirect()->back()->with('success', 'Riwayat telah berhasil dihapus.');
    }

    // ... (Sisa method store, approve, reject tidak berubah)
    
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'project_id' => 'required|exists:projects,id',
            'requested_user_id' => 'required|exists:users,id',
            'message' => 'nullable|string|max:1000',
        ]);
        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'Data tidak valid.', 'errors' => $validator->errors()], 422);
        }

        $requestedUser = User::findOrFail($request->requested_user_id);
        $approver = $this->findCoordinator($requestedUser);

        if (!$approver) {
            return response()->json(['success' => false, 'message' => 'Koordinator untuk anggota ini tidak dapat ditemukan.'], 422);
        }

        $peminjamanRequest = PeminjamanRequest::create($request->all() + ['requester_id' => Auth::id(), 'approver_id' => $approver->id, 'due_date' => now()->addWeekday()]);
        Notification::send($approver, new PeminjamanRequested($peminjamanRequest));
        return response()->json(['success' => true, 'message' => 'Permintaan peminjaman anggota telah berhasil dikirim.']);
    }
    
    private function findCoordinator(User $user): ?User
    {
        // PERBAIKAN: Menggunakan hierarki Unit, bukan hierarki User->parent yang sudah tidak ada.
        // Eager load relasi untuk menghindari N+1 query.
        $user->load('unit.parentUnit.parentUnit'); // Load beberapa level ke atas.

        $currentUnit = $user->unit;

        while ($currentUnit) {
            // Cari approver (Koordinator atau Eselon II) di unit saat ini.
            $approver = User::where('unit_id', $currentUnit->id)
                ->whereIn('role', [User::ROLE_KOORDINATOR, User::ROLE_ESELON_II])
                ->orderByRaw("FIELD(role, '".User::ROLE_KOORDINATOR."', '".User::ROLE_ESELON_II."')") // Prioritaskan Koordinator
                ->first();

            if ($approver) {
                return $approver;
            }

            // Jika tidak ditemukan, pindah ke unit induk.
            $currentUnit = $currentUnit->parentUnit;
        }

        // Jika sampai ke puncak hierarki dan tidak ditemukan, kembalikan null.
        return null;
    }

    public function approve(PeminjamanRequest $peminjamanRequest)
    {
        if ($peminjamanRequest->approver_id !== Auth::id()) abort(403);
        try {
            $project = Project::withoutGlobalScope(HierarchicalScope::class)->findOrFail($peminjamanRequest->project_id);
            $project->members()->syncWithoutDetaching([$peminjamanRequest->requested_user_id]);
            $peminjamanRequest->update(['status' => 'approved']);
            if ($peminjamanRequest->requester) Notification::send($peminjamanRequest->requester, new PeminjamanApproved($peminjamanRequest));
            return redirect()->route('peminjaman-requests.my-requests')->with('success', 'Permintaan telah disetujui.');
        } catch (ModelNotFoundException $e) {
            return redirect()->route('peminjaman-requests.my-requests')->with('error', 'Gagal menyetujui: Proyek terkait tidak ditemukan.');
        }
    }

    public function reject(Request $request, PeminjamanRequest $peminjamanRequest)
    {
        if ($peminjamanRequest->approver_id !== Auth::id()) abort(403);
        $request->validate(['rejection_reason' => 'required|string|max:1000']);
        $peminjamanRequest->update($request->only('rejection_reason') + ['status' => 'rejected']);
        if ($peminjamanRequest->requester) Notification::send($peminjamanRequest->requester, new PeminjamanRejected($peminjamanRequest));
        return redirect()->route('peminjaman-requests.my-requests')->with('success', 'Permintaan telah ditolak.');
    }
}