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
        Gate::authorize('view', $task->project);

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
        Gate::authorize('view', $attachment->task->project);

        Storage::disk('public')->delete($attachment->path);
        $attachment->delete();

        return back()->with('success', 'File berhasil dihapus.');
    }
}
