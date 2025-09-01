<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Symfony\Component\ExpressionLanguage\SyntaxError;

class SettingController extends Controller
{
    use AuthorizesRequests;

    public function index()
    {
        $settings = Setting::pluck('value', 'key')->all();
        return view('admin.settings.index', compact('settings'));
    }

    public function formulas()
    {
        $this->authorize('manage_settings');
        $settings = Setting::pluck('value', 'key')->all();
        return view('admin.settings.formulas', compact('settings'));
    }

    public function update(Request $request, ExpressionLanguage $expressionLanguage)
    {
        $this->authorize('manage_settings');
        $data = $request->except('_token');

        // --- Formula Validation ---
        $formulasToValidate = [
            'iki_formula' => ['base_score', 'efficiency_factor', 'capped_efficiency_factor'],
            'nkf_formula_staf' => ['individual_score'],
            'nkf_formula_pimpinan' => ['individual_score', 'managerial_score', 'weight'],
        ];

        foreach ($formulasToValidate as $key => $allowedVars) {
            if ($request->has($key)) {
                $formula = $request->input($key);
                try {
                    $parsed = $expressionLanguage->parse($formula, $allowedVars);
                    // Check if all used variables are allowed
                    $usedVars = $parsed->getNodes()->getVariableNames();
                    if (array_diff($usedVars, $allowedVars)) {
                        return back()->withInput()->withErrors([$key => 'Rumus mengandung variabel yang tidak diizinkan.']);
                    }
                } catch (SyntaxError $e) {
                    return back()->withInput()->withErrors([$key => 'Sintaks rumus tidak valid: ' . $e->getMessage()]);
                }
            }
        }
        // --- End Formula Validation ---

        if ($request->hasFile('logo_path')) {
            $path = $request->file('logo_path')->store('logos', 'public');
            $data['logo_path'] = $path;
            $oldLogo = Setting::where('key', 'logo_path')->first();
            if ($oldLogo && $oldLogo->value) {
                Storage::disk('public')->delete($oldLogo->value);
            }
        }

        foreach ($data as $key => $value) {
            Setting::updateOrCreate(['key' => $key], ['value' => strval($value)]);
        }

        // Redirect back to the correct settings page
        if ($request->has('iki_formula')) {
            return redirect()->route('admin.settings.formulas')->with('success', 'Pengaturan rumus berhasil diperbarui.');
        }

        return redirect()->route('admin.settings.index')->with('success', 'Pengaturan berhasil diperbarui.');
    }

    public function simulate(Request $request, ExpressionLanguage $expressionLanguage)
    {
        $this->authorize('manage_settings');

        $validated = $request->validate([
            'formula' => 'required|string',
            'variables' => 'required|array'
        ]);

        $variables = array_map('floatval', $validated['variables']);
        $allowedVars = array_keys($variables);

        try {
            $parsed = $expressionLanguage->parse($validated['formula'], $allowedVars);
            $usedVars = $parsed->getNodes()->getVariableNames();
            if (array_diff($usedVars, $allowedVars)) {
                return response()->json(['error' => 'Rumus mengandung variabel yang tidak diizinkan.'], 422);
            }
            $result = $expressionLanguage->evaluate($validated['formula'], $variables);
            return response()->json(['result' => $result]);
        } catch (SyntaxError $e) {
            return response()->json(['error' => 'Sintaks rumus tidak valid: ' . $e->getMessage()], 422);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Gagal mengevaluasi rumus: ' . $e->getMessage()], 500);
        }
    }
}
