<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\NotificationTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class NotificationTemplateController extends Controller
{
    public function index()
    {
        Gate::authorize('manage_settings');
        $templates = NotificationTemplate::all();
        return view('admin.notification_templates.index', compact('templates'));
    }

    public function edit(NotificationTemplate $notificationTemplate)
    {
        Gate::authorize('manage_settings');
        return view('admin.notification_templates.edit', compact('notificationTemplate'));
    }

    public function update(Request $request, NotificationTemplate $notificationTemplate)
    {
        Gate::authorize('manage_settings');
        $validated = $request->validate([
            'subject' => 'required|string|max:255',
            'body' => 'required|string',
            'description' => 'nullable|string',
        ]);

        $notificationTemplate->update($validated);

        return redirect()->route('admin.notification-templates.index')->with('success', 'Template notifikasi berhasil diperbarui.');
    }
}
