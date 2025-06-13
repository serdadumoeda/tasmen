<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class CommentController extends Controller
{
    public function store(Request $request, Task $task)
    {
        Gate::authorize('view', $task->project);

        $request->validate(['body' => 'required|string']);

        $task->comments()->create([
            'body' => $request->body,
            'user_id' => auth()->id()
        ]);

            // Beri notifikasi pada pemilik tugas dan ketua proyek, kecuali jika mereka yang berkomentar
        $recipients = collect([$task->assignedTo, $task->project->leader])
                        ->unique('id')
                        ->where('id', '!=', auth()->id());

        foreach ($recipients as $recipient) {
            $recipient->notify(new NewCommentOnTask($comment));
        }

        return back()->with('success', 'Komentar berhasil ditambahkan.');
    }
}
