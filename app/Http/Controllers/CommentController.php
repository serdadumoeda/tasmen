<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use App\Notifications\NewCommentOnTask; // PASTIKAN BARIS INI BENAR

class CommentController extends Controller
{
    public function store(Request $request, Task $task)
    {
        Gate::authorize('view', $task->project);

        $request->validate(['body' => 'required|string']);

        $comment = $task->comments()->create([
            'body' => $request->body,
            'user_id' => auth()->id()
        ]);

        // Beri notifikasi pada pemilik tugas dan ketua proyek, kecuali jika mereka yang berkomentar
        $recipients = $task->assignees->push($task->project->leader) // Ambil semua assignee, lalu tambahkan leader
                ->unique('id') // Pastikan tidak ada duplikat
                ->where('id', '!=', auth()->id()); // Jangan kirim notif ke diri sendiri

        Notification::send($recipients, new NewCommentOnTask($comment));
        
        return back()->with('success', 'Komentar berhasil ditambahkan.');
    }
}