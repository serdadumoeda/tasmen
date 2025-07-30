<?php

namespace App\Http\Controllers;

use App\Models\Attachment;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;

class AttachmentController extends Controller
{
    public function store(Request $request, Task $task)
    {
        Gate::authorize('update', $task);

        $request->validate([
            'file' => 'required|file|max:5120' // Maksimal 5MB
        ]);

        $file = $request->file('file');
        $path = $file->store('attachments', 'public');

        $task->attachments()->create([
            'user_id' => auth()->id(),
            'filename' => $file->getClientOriginalName(),
            'path' => $path
        ]);

        return back()->with('success', 'File berhasil diunggah.');
    }

    public function destroy(Attachment $attachment)
    {
       

        $task = $attachment->task;

        // Jika karena suatu hal tugasnya tidak ada, izinkan penghapusan untuk membersihkan data.
        if (!$task) {
            if (Storage::disk('public')->exists($attachment->path)) {
                Storage::disk('public')->delete($attachment->path);
            }
            $attachment->delete();
            return back()->with('success', 'Lampiran berhasil dihapus.');
        }

        // MODIFIKASI: Otorisasi berdasarkan hak 'update' pada tugas terkait.
        Gate::authorize('update', $task);

        // Hapus file dari storage
        if (Storage::disk('public')->exists($attachment->path)) {
            Storage::disk('public')->delete($attachment->path);
        }
        
        // Hapus record dari database
        $attachment->delete();

        return back()->with('success', 'File berhasil dihapus.');

    }
}