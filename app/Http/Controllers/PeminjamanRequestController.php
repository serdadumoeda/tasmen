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
use App\Models\TemplateSurat;
use App\Scopes\HierarchicalScope;
use App\Services\ApprovalHierarchyService;
use App\Services\NomorSuratService;
use App\Services\SuratPeminjamanGenerator;
use App\Notifications\PeminjamanRequested;
use App\Notifications\PeminjamanApproved;
use App\Notifications\PeminjamanRejected;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class PeminjamanRequestController extends Controller
{
    use AuthorizesRequests;

    protected $approvalHierarchyService;

    public function __construct(ApprovalHierarchyService $approvalHierarchyService)
    {
        $this->approvalHierarchyService = $approvalHierarchyService;
    }

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
     * Menampilkan form untuk memulai permintaan peminjaman,
     * yang akan dialihkan ke pembuatan surat.
     */
    public function create()
    {
        $this->authorize('create', PeminjamanRequest::class);

        // Cari template surat untuk peminjaman pegawai
        $template = TemplateSurat::where('jenis', 'peminjaman_pegawai')->first();

        if (!$template) {
            // Beri pesan error jika admin belum membuat templatenya
            return redirect()->route('peminjaman-requests.index')
                ->with('error', 'Template surat untuk peminjaman pegawai belum dibuat oleh Admin.');
        }

        // Ambil daftar user yang bisa dipinjam
        $availableUsers = User::where('status', 'active')->where('id', '!=', auth()->id())->get();

        // Redirect ke form pembuatan surat keluar dengan parameter
        return redirect()->route('surat-keluar.create.from-template', [
            'template_id' => $template->id,
            'prefill_data' => [
                'peminjam_id' => auth()->id(),
                // Anda bisa tambahkan prefill lain jika perlu
            ]
        ]);
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

    // The store method is now obsolete as the creation is handled via the official letter flow.
    // public function store(...) { ... }

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