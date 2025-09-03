<?php

namespace App\Http\Controllers;

use App\Models\Surat;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SuratTaskController extends Controller
{
    /**
     * Create a new ad-hoc task from a given letter.
     *
     * @param \App\Models\Surat $surat
     * @return \Illuminate\Http\RedirectResponse
     */
    public function create(Surat $surat)
    {
        // Create a new Task
        $task = new Task();

        // Pre-populate data from the letter
        $task->title = 'Tindak Lanjut Surat: ' . $surat->perihal;
        $task->description = "Tugas ini dibuat sebagai tindak lanjut dari surat dengan nomor " . $surat->nomor_surat . ".\n\n" .
                             "Perihal: " . $surat->perihal . "\n\n" .
                             "Silakan lihat detail surat untuk informasi lebih lanjut.";

        // Set the creator of the task
        $task->creator_id = Auth::id();

        // Check if the letter is associated with a project and link it
        if ($surat->suratable_type === 'App\\Models\\Project' && $surat->suratable_id) {
            $task->project_id = $surat->suratable_id;
        } else {
            // This is an ad-hoc task not linked to a specific project
            $task->project_id = null;
        }

        // Link the task back to the source letter
        $task->surat_id = $surat->id;

        // Set an initial status and priority
        $task->status = 'pending';
        $task->priority = 'medium';

        $task->save();

        // Redirect to the task edit page so the user can complete the details
        return redirect()->route('tasks.edit', $task)->with('success', 'Tugas berhasil dibuat dari surat. Silakan lengkapi detail tugas.');
    }
}
