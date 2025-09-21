<?php

namespace App\Http\Controllers;

use App\Models\Berkas;
use App\Models\Surat;
use App\Models\Task;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Services\PageTitleService;
use App\Services\BreadcrumbService;
use Illuminate\Support\Str;

class SuratController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $currentUser = Auth::user();
        $query = Surat::query();

        if (!$currentUser->isSuperAdmin()) {
            // Get all user IDs in the current user's hierarchy (self + subordinates)
            $subordinateIds = $currentUser->getAllSubordinateIds();
            $relevantUserIds = $subordinateIds->push($currentUser->id);

            $query->where(function ($q) use ($relevantUserIds) {
                // Condition 1: Surat was created by the user or their subordinates.
                $q->whereIn('pembuat_id', $relevantUserIds);

                // Condition 2: Surat was dispositioned to the user or their subordinates.
                $q->orWhereHas('disposisi', function ($subQuery) use ($relevantUserIds) {
                    $subQuery->whereIn('penerima_id', $relevantUserIds);
                });
            });
        }

        $suratItems = $query->with('pembuat')->latest()->paginate(15);
        return view('surat.index', ['surat' => $suratItems]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $berkasList = Berkas::where('user_id', Auth::id())->orderBy('name')->get();
        return view('surat.create', compact('berkasList'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'perihal' => 'required|string|max:255',
            'nomor_surat' => 'nullable|string|max:255|unique:surat,nomor_surat',
            'tanggal_surat' => 'required|date',
            'file' => 'required|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:10240', // Max 10MB
            'berkas_id' => 'nullable|exists:berkas,id',
        ]);

        $path = $request->file('file')->store('surat_files', 'local');

        $surat = Surat::create([
            'perihal' => $validated['perihal'],
            'nomor_surat' => $validated['nomor_surat'] ?? null,
            'tanggal_surat' => $validated['tanggal_surat'],
            'file_path' => $path,
            'status' => 'draft',
            'pembuat_id' => Auth::id(),
        ]);

        // --- Handle Archiving ---
        if ($request->filled('berkas_id')) {
            $berkas = Berkas::find($validated['berkas_id']);
            // Ensure the user owns the folder
            if ($berkas && $berkas->user_id == Auth::id()) {
                $surat->update([
                    'berkas_id' => $berkas->id,
                    'status' => 'diarsipkan'
                ]);
                return redirect()->route('arsip.index')->with('success', 'Surat berhasil diunggah dan langsung diarsipkan.');
            }
        }
        // --- End Handle Archiving ---

        return redirect()->route('surat.index')->with('success', 'Surat berhasil diunggah dan dicatat.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Surat $surat)
    {
        $surat->load(['pembuat', 'disposisi' => function ($query) {
            $query->with(['pengirim', 'penerima', 'tembusanUsers', 'children' => function($q) {
                $q->with('penerima', 'tembusanUsers', 'children');
            }]);
        }]);

        $allUsers = User::orderBy('name')->get();
        $topLevelDisposisi = $surat->disposisi->where('parent_id', null);
        $parentDisposisi = $surat->disposisi->firstWhere('penerima_id', Auth::id());
        $berkasList = Berkas::where('user_id', Auth::id())->orderBy('name')->get();

        $breadcrumbs = [
            ['title' => 'Daftar Surat', 'url' => route('surat.index')],
            ['title' => Str::limit($surat->perihal, 40)],
        ];

        return view('surat.show', [
            'surat' => $surat,
            'dispositionUsers' => $allUsers,
            'topLevelDisposisi' => $topLevelDisposisi,
            'parentDisposisi' => $parentDisposisi,
            'breadcrumbs' => $breadcrumbs,
            'berkasList' => $berkasList,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Surat $surat)
    {
        if ($surat->file_path) {
            Storage::disk('local')->delete($surat->file_path);
        }
        $surat->delete();
        return redirect()->route('surat.index')->with('success', 'Surat berhasil dihapus.');
    }

    /**
     * Handle file download for a letter.
     */
    public function download(Surat $surat)
    {
        if (!$surat->file_path || !Storage::disk('local')->exists($surat->file_path)) {
            return back()->with('error', 'File tidak ditemukan.');
        }
        return Storage::disk('local')->download($surat->file_path);
    }

    /**
     * Show the form for creating a new task from a letter.
     */
    public function showMakeTaskForm(Surat $surat)
    {
        return view('surat.make-task', ['surat' => $surat]);
    }

    /**
     * Create a new task from a letter.
     */
    public function makeTask(Request $request, Surat $surat)
    {
        $defaultStatus = \App\Models\TaskStatus::where('key', 'pending')->firstOrFail();
        $defaultPriority = \App\Models\PriorityLevel::where('name', 'Normal')->firstOrFail();

        $task = Task::create([
            'title' => $surat->perihal,
            'description' => 'Tugas ini dibuat berdasarkan surat dengan perihal: ' . $surat->perihal . '. Lihat surat terlampir untuk detail.',
            'creator_id' => Auth::id(),
            'task_status_id' => $defaultStatus->id,
            'priority_level_id' => $defaultPriority->id,
            'deadline' => now()->addDays(7),
            'surat_id' => $surat->id,
        ]);

        $task->assignees()->attach(Auth::id());

    // Automatically attach the letter's file to the new task
    if ($surat->file_path) {
        $task->attachments()->create([
            'user_id' => Auth::id(),
            'filename' => 'Surat Asal - ' . Str::slug($surat->perihal) . '.' . pathinfo($surat->file_path, PATHINFO_EXTENSION),
            'path' => $surat->file_path,
        ]);
    }

    $task->load('status');

        $surat->status = 'disetujui';
        $surat->save();

        // Refresh the model to ensure all attributes (especially dates) are properly cast.
        $task->refresh();

        return redirect()->route('adhoc-tasks.edit', $task)->with('success', 'Tugas berhasil dibuat dari surat. Silakan lengkapi detail tugas.');
    }

    /**
     * Display the workflow page for the mail module.
     */
    public function showWorkflow()
    {
        return view('surat.workflow');
    }

    /**
     * Show the form for creating a new project from a letter.
     */
    public function makeProject(Surat $surat)
    {
        return redirect()->route('projects.create.step1')
            ->with('surat_id', $surat->id)
            ->with('prefill_name', $surat->perihal);
    }
}
