<?php

namespace App\Http\Controllers;

use App\Models\Surat;
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
        // Display a unified list of all letters, newest first.
        $suratItems = Surat::with('pembuat')->latest()->paginate(15);
        return view('surat.index', ['surat' => $suratItems]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('surat.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'perihal' => 'required|string|max:255',
            'tanggal_surat' => 'required|date',
            'file' => 'required|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:10240', // Max 10MB
        ]);

        $path = $request->file('file')->store('surat_files', 'private');

        Surat::create([
            'perihal' => $validated['perihal'],
            'tanggal_surat' => $validated['tanggal_surat'],
            'file_path' => $path,
            'status' => 'Baru',
            'pembuat_id' => Auth::id(),
        ]);

        return redirect()->route('surat.index')->with('success', 'Surat berhasil diunggah dan dicatat.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Surat $surat)
    {
        $surat->load(['pembuat', 'disposisi' => function ($query) {
            $query->with(['pengirim', 'penerima', 'tembusanUsers', 'children' => function($q) {
                $q->with('penerima', 'tembusanUsers', 'children'); // Recursive eager loading
            }]);
        }]);

        $allUsers = User::orderBy('name')->get();
        $topLevelDisposisi = $surat->disposisi->where('parent_id', null);
        $parentDisposisi = $surat->disposisi->firstWhere('penerima_id', Auth::id());

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
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Surat $surat)
    {
        // We might want to add authorization here later, e.g.,
        // $this->authorize('delete', $surat);

        if ($surat->file_path) {
            Storage::disk('private')->delete($surat->file_path);
        }

        $surat->delete();

        return redirect()->route('surat.index')->with('success', 'Surat berhasil dihapus.');
    }

    /**
     * Handle file download for a letter.
     */
    public function download(Surat $surat)
    {
        if (!$surat->file_path || !Storage::disk('private')->exists($surat->file_path)) {
            return back()->with('error', 'File tidak ditemukan.');
        }

        return Storage::disk('private')->download($surat->file_path);
    }

    /**
     * Create a new task from a letter.
     */
    public function makeTask(Request $request, Surat $surat)
    {
        // Create a new ad-hoc task
        $task = \App\Models\Task::create([
            'title' => $surat->perihal,
            'description' => 'Tugas ini dibuat berdasarkan surat dengan perihal: ' . $surat->perihal . '. Lihat surat terlampir untuk detail.',
            'creator_id' => Auth::id(),
            'status_id' => 1, // Assuming 1 is 'To Do' or 'Baru'
            'priority_id' => 2, // Assuming 2 is 'Normal'
            'due_date' => now()->addDays(7), // Default due date
            'surat_id' => $surat->id,
        ]);

        // Assign the current user to the task by default
        $task->assignees()->attach(Auth::id());

        // Update the letter's status
        $surat->status = 'Ditugaskan';
        $surat->save();

        return redirect()->route('tasks.edit', $task)->with('success', 'Tugas berhasil dibuat dari surat. Silakan lengkapi detail tugas.');
    }

    /**
     * Display the workflow page for letters.
     */
    public function showWorkflow(PageTitleService $pageTitleService, BreadcrumbService $breadcrumbService)
    {
        $pageTitleService->setTitle('Alur Kerja Surat');
        $breadcrumbService->add('Surat', route('surat.index'));
        $breadcrumbService->add('Alur Kerja');
        return view('surat.workflow');
    }
}
