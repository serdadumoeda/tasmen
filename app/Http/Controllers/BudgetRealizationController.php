<?php

namespace App\Http\Controllers;

use App\Models\BudgetItem;
use App\Models\BudgetRealization;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class BudgetRealizationController extends Controller
{
    // 2. TAMBAHKAN TRAIT INI
    use AuthorizesRequests;

    public function store(Request $request, BudgetItem $budgetItem)
    {
        // Sekarang $this->authorize() akan berfungsi
        $project = $budgetItem->project;
        $this->authorize('update', $project);

        $validated = $request->validate([
            'amount' => 'required|numeric|min:0',
            'transaction_date' => 'required|date',
            'description' => 'nullable|string',
            // 'receipt_path' => 'nullable|file|mimes:jpg,png,pdf|max:2048' // Contoh jika ada upload
        ]);

        // Pastikan total realisasi tidak melebihi sisa anggaran (opsional, tapi direkomendasikan)
        $sisaAnggaran = $budgetItem->remaining_cost;
        if ($validated['amount'] > $sisaAnggaran) {
             // Beri peringatan, tapi tetap lanjutkan. Atau batalkan dengan validasi.
             // return back()->with('error', 'Jumlah realisasi melebihi sisa anggaran!');
        }


        $budgetItem->realizations()->create([
            'amount' => $validated['amount'],
            'transaction_date' => $validated['transaction_date'],
            'description' => $validated['description'],
            'user_id' => Auth::id(),
            // Logika untuk menyimpan file jika ada
        ]);

        return back()->with('success', 'Realisasi anggaran berhasil dicatat.');
    }

    public function destroy(BudgetRealization $realization)
    {
        // Saya juga memperbaiki method destroy untuk Anda
        $project = $realization->budgetItem->project;
        $this->authorize('update', $project);
        
        $realization->delete();
        
        return back()->with('success', 'Data realisasi berhasil dihapus.');
    }
}