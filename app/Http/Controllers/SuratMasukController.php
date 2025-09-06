<?php

namespace App\Http\Controllers;

use App\Models\Disposisi;
use App\Models\Surat;
use App\Models\LampiranSurat;
use App\Models\User;
use App\Notifications\SuratDisposisiNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Services\PageTitleService;
use App\Services\BreadcrumbService;
use Illuminate\Support\Str;

class SuratMasukController extends Controller
{
    public function index()
    {
        $suratMasuk = Surat::where('jenis', 'masuk')->latest()->paginate(15);
        return view('suratmasuk.index', compact('suratMasuk'));
    }

    public function create()
    {
        return view('suratmasuk.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'perihal' => 'required|string|max:255',
            'nomor_surat' => 'required|string|max:255|unique:surat,nomor_surat',
            'tanggal_surat' => 'required|date',
            'lampiran' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120', // Max 5MB
        ]);

        $user = Auth::user();

        $surat = Surat::create([
            'perihal' => $validated['perihal'],
            'nomor_surat' => $validated['nomor_surat'],
            'tanggal_surat' => $validated['tanggal_surat'],
            'jenis' => 'masuk',
            'status' => 'diarsipkan', // Default status for incoming mail
            'pembuat_id' => $user->id,
        ]);

        if ($request->hasFile('lampiran')) {
            $file = $request->file('lampiran');
            $path = $file->store('lampiran-surat', 'public');

            LampiranSurat::create([
                'surat_id' => $surat->id,
                'nama_file' => $file->getClientOriginalName(),
                'path_file' => $path,
                'tipe_file' => $file->getClientMimeType(),
                'ukuran_file' => $file->getSize(),
            ]);
        }

        // --- AUTOMATIC DISPOSITION ---
        // Automatically create a disposition to the head of the user's unit.
        $user->load('unit');
        if ($user->unit && $user->unit->kepala_unit_id) {
            $kepalaUnitId = $user->unit->kepala_unit_id;

            $disposisi = Disposisi::create([
                'surat_id' => $surat->id,
                'pengirim_id' => $user->id,
                'penerima_id' => $kepalaUnitId,
                'instruksi' => 'Mohon arahan dan petunjuk selanjutnya.',
                'tanggal_disposisi' => now(),
            ]);

            // Notify the unit head
            $kepalaUnit = User::find($kepalaUnitId);
            if ($kepalaUnit) {
                // Assuming SuratDisposisiNotification exists and accepts a Disposisi object.
                $kepalaUnit->notify(new SuratDisposisiNotification($disposisi));
            }
        }
        // --- END AUTOMATIC DISPOSITION ---


        return redirect()->route('surat-masuk.index')->with('success', 'Surat masuk berhasil diarsipkan dan disposisi otomatis telah dibuat.');
    }

    public function show(Surat $surat_masuk)
    {
        $surat = $surat_masuk;
        if ($surat->jenis !== 'masuk') {
            abort(404);
        }

        // Get all users for the selection dropdowns
        $allUsers = User::orderBy('name')->get();

        // Eager load relationships for display
        $surat->load(['lampiran', 'disposisi' => function ($query) {
            // Load the full hierarchy
            $query->with(['pengirim', 'penerima', 'tembusanUsers', 'children' => function($q) {
                $q->with('penerima', 'tembusanUsers', 'children'); // Recursive eager loading
            }]);
        }]);

        // Get only the top-level dispositions to start rendering the tree
        $topLevelDisposisi = $surat->disposisi->where('parent_id', null);

        // Find the disposition that was sent to the current user, to be used as parent
        $parentDisposisi = $surat->disposisi->firstWhere('penerima_id', Auth::id());

        $breadcrumbs = [
            ['title' => 'Surat Masuk', 'url' => route('surat-masuk.index')],
            ['title' => Str::limit($surat->perihal, 40)],
        ];

        return view('suratmasuk.show', [
            'surat' => $surat,
            'dispositionUsers' => $allUsers, // All users for selection
            'topLevelDisposisi' => $topLevelDisposisi,
            'parentDisposisi' => $parentDisposisi,
            'breadcrumbs' => $breadcrumbs,
        ]);
    }

    public function destroy(Surat $surat)
    {
        $this->authorize('delete', $surat);

        // Hapus file lampiran dari storage
        foreach ($surat->lampiran as $lampiran) {
            Storage::disk('public')->delete($lampiran->path_file);
        }

        $surat->delete();

        return redirect()->route('surat-masuk.index')->with('success', 'Surat masuk berhasil dihapus.');
    }

    public function showWorkflow(PageTitleService $pageTitleService, BreadcrumbService $breadcrumbService)
    {
        $pageTitleService->setTitle('Alur Kerja Surat Masuk');
        $breadcrumbService->add('Surat Masuk', route('surat-masuk.index'));
        $breadcrumbService->add('Alur Kerja');
        return view('suratmasuk.workflow');
    }
}
