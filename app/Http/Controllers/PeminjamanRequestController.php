<?php

namespace App\Http\Controllers;

use App\Enums\RequestStatus;
use App\Models\PeminjamanRequest;
use App\Models\Project;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Models\Surat;
use App\Scopes\HierarchicalScope;
use App\Services\NomorSuratService;
use App\Services\SuratPeminjamanGenerator;
use App\Notifications\PeminjamanRequested;
use App\Notifications\PeminjamanApproved;
use App\Notifications\PeminjamanRejected;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class PeminjamanRequestController extends Controller
{
    use AuthorizesRequests;

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
            ->where('status', RequestStatus::PENDING)
            ->with(['project' => fn($query) => $query->withoutGlobalScope(HierarchicalScope::class), 'requester', 'requestedUser'])
            ->latest()
            ->get();

        // 2. Ambil riwayat permintaan yang SAYA ajukan (dengan pagination)
        $mySentRequests = PeminjamanRequest::where('requester_id', $userId)
            ->with(['project', 'requestedUser', 'approver', 'surat'])
            ->latest()
            ->paginate(config('tasmen.pagination.loan_requests', 5), ['*'], 'sent_page');

        // 3. Ambil riwayat persetujuan yang TELAH SAYA PROSES (dengan pagination)
        $approvalHistory = PeminjamanRequest::where('approver_id', $userId)
            ->whereIn('status', [RequestStatus::APPROVED->value, RequestStatus::REJECTED->value]) // Hanya yang sudah disetujui/ditolak
            ->with(['project', 'requester', 'requestedUser', 'surat'])
            ->latest()
            ->paginate(config('tasmen.pagination.loan_requests', 5), ['*'], 'history_page');

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
        $this->authorize('delete', $peminjamanRequest);

        $peminjamanRequest->delete();

        return redirect()->back()->with('success', 'Riwayat telah berhasil dihapus.');
    }

    public function store(Request $request, SuratPeminjamanGenerator $suratGenerator)
    {
        $this->authorize('create', PeminjamanRequest::class);

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

        $peminjamanRequest = PeminjamanRequest::create($request->all() + [
            'requester_id' => Auth::id(),
            'approver_id' => $approver->id,
            'due_date' => now()->addWeekdays(config('tasmen.loan_request_due_days', 3))
        ]);

        // Generate the draft letter
        try {
            $suratGenerator->generate($peminjamanRequest);
        } catch (\Exception $e) {
            // Log the error but don't fail the main request
            \Illuminate\Support\Facades\Log::error('Gagal membuat draf surat peminjaman: ' . $e->getMessage());
        }

        Notification::send($approver, new PeminjamanRequested($peminjamanRequest));

        return response()->json(['success' => true, 'message' => 'Permintaan peminjaman anggota telah berhasil dikirim.']);
    }

    private function findCoordinator(User $user): ?User
    {
        // PERBAIKAN: Menggunakan hierarki Unit dan relasi Role yang baru.
        $user->load('unit.parentUnit.parentUnit'); // Load beberapa level ke atas.

        $currentUnit = $user->unit;

        while ($currentUnit) {
            // Cari approver (Koordinator atau Eselon II) di unit saat ini.
            $approver = User::where('unit_id', $currentUnit->id)
                ->whereHas('roles', function ($query) {
                    $query->whereIn('name', ['Koordinator', 'Eselon II']);
                })
                ->with('roles') // Eager load roles to use in orderBy
                ->get()
                ->sortBy(function ($user) {
                    if ($user->hasRole('Koordinator')) {
                        return 1;
                    }
                    if ($user->hasRole('Eselon II')) {
                        return 2;
                    }
                    return 3;
                })
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

    public function approve(PeminjamanRequest $peminjamanRequest, NomorSuratService $nomorSuratService)
    {
        $this->authorize('approve', $peminjamanRequest);

        try {
            DB::transaction(function () use ($peminjamanRequest, $nomorSuratService) {
                $project = Project::withoutGlobalScope(HierarchicalScope::class)->findOrFail($peminjamanRequest->project_id);
                $project->members()->syncWithoutDetaching([$peminjamanRequest->requested_user_id]);
                $peminjamanRequest->update(['status' => RequestStatus::APPROVED]);

                // Finalize the associated letter
                $surat = $peminjamanRequest->surat;
                if ($surat) {
                    $surat->status = 'disetujui';
                    // Generate number only if it doesn't have one
                    if (!$surat->nomor_surat) {
                        $surat->nomor_surat = $nomorSuratService->generate($surat->klasifikasi, $surat->pembuat);
                    }
                    $surat->save();
                    $peminjamanRequest->recordActivity('approved_peminjaman_surat');
                }
            });

            if ($peminjamanRequest->requester) {
                Notification::send($peminjamanRequest->requester, new PeminjamanApproved($peminjamanRequest));
            }

            return redirect()->route('peminjaman-requests.my-requests')->with('success', 'Permintaan telah disetujui dan surat resmi telah difinalisasi.');

        } catch (ModelNotFoundException $e) {
            return redirect()->route('peminjaman-requests.my-requests')->with('error', 'Gagal menyetujui: Proyek terkait tidak ditemukan.');
        } catch (\Exception $e) {
            return redirect()->route('peminjaman-requests.my-requests')->with('error', 'Terjadi kesalahan saat finalisasi surat: ' . $e->getMessage());
        }
    }

    public function reject(Request $request, PeminjamanRequest $peminjamanRequest)
    {
        $this->authorize('reject', $peminjamanRequest);

        $request->validate(['rejection_reason' => 'required|string|max:1000']);

        $peminjamanRequest->update($request->only('rejection_reason') + ['status' => RequestStatus::REJECTED]);

        if ($peminjamanRequest->requester) {
            Notification::send($peminjamanRequest->requester, new PeminjamanRejected($peminjamanRequest));
        }

        return redirect()->route('peminjaman-requests.my-requests')->with('success', 'Permintaan telah ditolak.');
    }
}