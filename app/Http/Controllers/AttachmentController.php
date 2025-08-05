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

        $attachment = $task->attachments()->create([
            'user_id' => auth()->id(),
            'filename' => $file->getClientOriginalName(),
            'path' => $path
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'File berhasil diunggah.',
                'attachment_html' => view('partials._attachment-item', compact('attachment'))->render()
            ]);
        }

        return back()->with('success', 'File berhasil diunggah.');
    }

    public function destroy(Attachment $attachment)
    {
        $task = $attachment->task;

        if ($task) {
            Gate::authorize('update', $task);
        }

        if (Storage::disk('public')->exists($attachment->path)) {
            Storage::disk('public')->delete($attachment->path);
        }
        
        $attachment->delete();

        if (request()->wantsJson()) {
            return response()->json(['message' => 'File berhasil dihapus.']);
        }

        return back()->with('success', 'File berhasil dihapus.');
    }
}