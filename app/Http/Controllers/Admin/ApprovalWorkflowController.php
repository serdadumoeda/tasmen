<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ApprovalWorkflow;
use App\Models\ApprovalWorkflowStep;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ApprovalWorkflowController extends Controller
{
    public function index()
    {
        $workflows = ApprovalWorkflow::withCount('steps')->orderBy('name')->get();
        return view('admin.approval-workflows.index', compact('workflows'));
    }

    public function create()
    {
        return view('admin.approval-workflows.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:approval_workflows,name',
            'description' => 'nullable|string',
        ]);

        $workflow = ApprovalWorkflow::create($validated);

        return redirect()->route('admin.approval-workflows.show', $workflow)->with('success', 'Alur kerja berhasil dibuat. Silakan tambahkan langkah-langkah persetujuan.');
    }

    public function show(ApprovalWorkflow $approvalWorkflow)
    {
        $approvalWorkflow->load('steps');
        $roles = User::ROLES; // Get all possible roles
        return view('admin.approval-workflows.show', compact('approvalWorkflow', 'roles'));
    }

    public function edit(ApprovalWorkflow $approvalWorkflow)
    {
        return view('admin.approval-workflows.edit', compact('approvalWorkflow'));
    }

    public function update(Request $request, ApprovalWorkflow $approvalWorkflow)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('approval_workflows')->ignore($approvalWorkflow->id)],
            'description' => 'nullable|string',
        ]);

        $approvalWorkflow->update($validated);

        return redirect()->route('admin.approval-workflows.show', $approvalWorkflow)->with('success', 'Alur kerja berhasil diperbarui.');
    }

    public function destroy(ApprovalWorkflow $approvalWorkflow)
    {
        // Add a check to ensure it's not in use by any unit
        if ($approvalWorkflow->units()->exists()) {
            return back()->with('error', 'Alur kerja ini tidak dapat dihapus karena sedang digunakan oleh satu atau lebih unit kerja.');
        }

        $approvalWorkflow->delete();

        return redirect()->route('admin.approval-workflows.index')->with('success', 'Alur kerja berhasil dihapus.');
    }

    public function addStep(Request $request, ApprovalWorkflow $approvalWorkflow)
    {
        $validated = $request->validate([
            'step' => ['required', 'integer', 'min:1', Rule::unique('approval_workflow_steps')->where('approval_workflow_id', $approvalWorkflow->id)],
            'approver_role' => ['required', 'string', Rule::in(array_column(User::ROLES, 'name'))],
            'is_final_approval' => ['nullable', 'boolean'],
        ]);

        $approvalWorkflow->steps()->create([
            'step' => $validated['step'],
            'approver_role' => $validated['approver_role'],
            'is_final_approval' => $request->has('is_final_approval'),
        ]);

        return redirect()->route('admin.approval-workflows.show', $approvalWorkflow)->with('success', 'Langkah persetujuan berhasil ditambahkan.');
    }

    public function destroyStep(ApprovalWorkflow $approvalWorkflow, ApprovalWorkflowStep $step)
    {
        // Ensure the step belongs to the workflow for security
        if ($step->approval_workflow_id !== $approvalWorkflow->id) {
            abort(404);
        }

        $step->delete();

        return redirect()->route('admin.approval-workflows.show', $approvalWorkflow)->with('success', 'Langkah persetujuan berhasil dihapus.');
    }
}
