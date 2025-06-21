<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\BudgetItem;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class BudgetItemController extends Controller
{
    use AuthorizesRequests;

    public function index(Project $project)
    {
        $this->authorize('view', $project);
        
        $budgetItems = $project->budgetItems()->orderBy('category')->get()->groupBy('category');
        $totalBudget = $project->budgetItems()->sum('total_cost');

        return view('projects.budget.index', compact('project', 'budgetItems', 'totalBudget'));
    }

    public function create(Project $project)
    {
        $this->authorize('update', $project);
        return view('projects.budget.create', compact('project'));
    }

    public function store(Request $request, Project $project)
    {
        $this->authorize('update', $project);

        $validated = $request->validate([
            'category' => 'required|string',
            'item_name' => 'required|string|max:255',
            'quantity' => 'required|integer|min:1',
            'frequency' => 'required|integer|min:1',
            'unit_price' => 'required|numeric|min:0',
            'description' => 'nullable|string',
        ]);

        $validated['project_id'] = $project->id;
        $validated['total_cost'] = $validated['quantity'] * $validated['frequency'] * $validated['unit_price'];

        BudgetItem::create($validated);

        return redirect()->route('projects.budget-items.index', $project)->with('success', 'Item anggaran berhasil ditambahkan.');
    }

    public function edit(Project $project, BudgetItem $budgetItem)
    {
        $this->authorize('update', $project);
        return view('projects.budget.edit', compact('project', 'budgetItem'));
    }

    public function update(Request $request, Project $project, BudgetItem $budgetItem)
    {
        $this->authorize('update', $project);

        $validated = $request->validate([
            'category' => 'required|string',
            'item_name' => 'required|string|max:255',
            'quantity' => 'required|integer|min:1',
            'frequency' => 'required|integer|min:1',
            'unit_price' => 'required|numeric|min:0',
            'description' => 'nullable|string',
        ]);
        
        $validated['total_cost'] = $validated['quantity'] * $validated['frequency'] * $validated['unit_price'];

        $budgetItem->update($validated);

        return redirect()->route('projects.budget-items.index', $project)->with('success', 'Item anggaran berhasil diperbarui.');
    }

    public function destroy(Project $project, BudgetItem $budgetItem)
    {
        $this->authorize('update', $project);
        $budgetItem->delete();
        return redirect()->route('projects.budget-items.index', $project)->with('success', 'Item anggaran berhasil dihapus.');
    }
}