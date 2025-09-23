<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Models\Jabatan;
use App\Models\Unit;
use App\Models\User as UserModel;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        $user = $request->user()->load(['jabatan', 'unit.ancestors']);

        $supervisors = UserModel::where('id', '!=', $user->id)
            ->orderBy('name')
            ->get();

        $rootUnit = Unit::whereNull('parent_unit_id')->first();
        $eselonIUnits = $rootUnit ? $rootUnit->childUnits()->orderBy('name')->get() : collect();

        $selectedUnitPath = [];
        if ($user->unit) {
            $ancestors = $user->unit->ancestors->sortBy('depth');
            // Drop root (depth 0) because dropdown starts from Eselon I
            $selectedUnitPath = $ancestors->pluck('id')->slice(1)->values()->toArray();
            $selectedUnitPath[] = $user->unit->id;
        }

        return view('profile.edit', compact('user', 'supervisors', 'eselonIUnits', 'selectedUnitPath'));
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user()->load(['jabatan', 'unit']);
        $validatedData = $request->validated();

        $jabatanName = $validatedData['jabatan_name'] ?? null;
        unset($validatedData['jabatan_name']);

        DB::transaction(function () use ($user, $validatedData, $jabatanName) {
            $unitId = $validatedData['unit_id'] ?? $user->unit_id;

            if ($unitId && $user->unit_id !== (int) $unitId) {
                $validatedData['atasan_id'] = null;
            }

            $user->fill($validatedData);

            if ($user->isDirty('email')) {
                $user->email_verified_at = null;
            }

            $user->save();

            if ($jabatanName && $unitId) {
                $existingCanManage = optional($user->jabatan)->can_manage_users ?? false;

                Jabatan::updateOrCreate(
                    ['user_id' => $user->id],
                    [
                        'name' => $jabatanName,
                        'unit_id' => $unitId,
                        'can_manage_users' => $existingCanManage,
                    ]
                );
            }

            UserModel::syncRoleFromUnit($user->fresh(['unit', 'jabatan']));
        });

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
