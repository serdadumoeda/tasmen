<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use App\Notifications\NewCommentOnTask;
use Illuminate\Support\Facades\Notification;

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

        // --- @mention Notification Logic ---
        preg_match_all('/@(\w+)/', $request->body, $matches);
        $mentionedUsernames = $matches[1];

        if (!empty($mentionedUsernames)) {
            $mentionedUsers = \App\Models\User::whereIn('name', $mentionedUsernames)->get();
            $mentioner = auth()->user();

            // Filter out the user who is making the comment
            $recipients = $mentionedUsers->where('id', '!=', $mentioner->id);

            if ($recipients->isNotEmpty()) {
                Notification::send($recipients, new \App\Notifications\UserMentioned($comment, $mentioner));
            }
        }

        // --- Original Notification Logic for Assignees ---
        // Beri notifikasi pada pemilik tugas dan ketua proyek, kecuali jika mereka yang berkomentar
        $assigneeRecipients = $task->assignees->push($task->project->leader)
                ->unique('id')
                ->where('id', '!=', auth()->id());

        if ($assigneeRecipients->isNotEmpty()) {
            Notification::send($assigneeRecipients, new NewCommentOnTask($comment));
        }

        if ($request->wantsJson()) {
            $commentHtml = view('partials._comment-item', ['comment' => $comment])->render();
            return response()->json([
                'message' => 'Komentar berhasil ditambahkan.',
                'comment_html' => $commentHtml
            ]);
        }
        
        return back()->with('success', 'Komentar berhasil ditambahkan.');
    }
}