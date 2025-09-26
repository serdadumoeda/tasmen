<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class ResourcePoolController extends Controller
{
    use AuthorizesRequests;

    /**
     * Menampilkan halaman manajemen resource pool.
     */
    public function index(Request $request)
    {
        $manager = Auth::user();
        $search = $request->input('search');

        if (!$manager->unit) {
            $paginator = new \Illuminate\Pagination\LengthAwarePaginator([], 0, 15, 1, [
                'path' => $request->url(),
                'query' => $request->query(),
            ]);
            return view('resource_pool.index', [
                'workloadData' => $paginator,
                'search' => $search,
            ]);
        }

        $unitIds = $manager->unit->getAllSubordinateUnitIds();
        $unitIds[] = $manager->unit->id;
        $teamMembersQuery = User::whereIn('unit_id', array_unique($unitIds))
                                ->where('id', '!=', $manager->id);

        if ($search) {
            // Case-insensitive search for name
            $teamMembersQuery->whereRaw('LOWER(name) LIKE ?', ['%' . strtolower($search) . '%']);
        }

        $paginatedMembers = $teamMembersQuery->latest('name')->paginate(15);

        $standardHours = config('tasmen.workload.standard_hours', 37.5);

        // Transform the collection within the paginator
        $workloadItems = $paginatedMembers->getCollection()->map(function ($member) use ($standardHours) {
            $totalAssignedHours = $member->tasks()
                ->whereHas('status', function ($q) {
                    $q->where('key', '!=', 'completed');
                })
                ->sum('estimated_hours');

            $workloadPercentage = ($standardHours > 0)
                ? ($totalAssignedHours / $standardHours) * 100
                : 0;

            return [
                'user' => $member,
                'workload_percentage' => round($workloadPercentage)
            ];
        });

        // Create a new paginator instance with the transformed items
        $paginatedWorkloadData = new \Illuminate\Pagination\LengthAwarePaginator(
            $workloadItems,
            $paginatedMembers->total(),
            $paginatedMembers->perPage(),
            $paginatedMembers->currentPage(),
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return view('resource_pool.index', [
            'workloadData' => $paginatedWorkloadData,
            'search' => $search,
        ]);
    }

    /**
     * Memperbarui status resource pool seorang pengguna.
     */
    public function update(Request $request, User $user)
    {
        // Prevent users from updating their own status, unless they are a Superadmin
        if (Auth::id() === $user->id && !Auth::user()->isSuperAdmin()) {
            return response()->json(['success' => false, 'message' => 'Anda tidak dapat mengubah status resource pool diri sendiri.'], 403);
        }

        if (!Auth::user()->isSuperAdmin() && !Auth::user()->is($user->atasan) && !$user->isSubordinateOf(Auth::user())) {
            return response()->json(['success' => false, 'message' => 'Anda tidak berwenang mengubah status pengguna ini.'], 403);
        }
        

        $request->validate([
            'is_in_resource_pool' => 'required|boolean',
            'pool_availability_notes' => 'nullable|string|max:500',
        ]);

        $user->update([
            'is_in_resource_pool' => $request->is_in_resource_pool,
            'pool_availability_notes' => $request->pool_availability_notes,
        ]);

        return response()->json(['success' => true, 'message' => 'Status anggota berhasil diperbarui.']);
    }
    /**
     * API untuk mengambil daftar anggota yang tersedia di pool
     * untuk digunakan di halaman pembuatan proyek.
     */
    public function getAvailableMembers()
    {
        $members = User::where('is_in_resource_pool', true)
                        ->where('id', '!=', Auth::id()) // Jangan tampilkan diri sendiri
                        ->with(['atasan', 'roles', 'jabatan']) // Muat relasi yang diperlukan untuk label
                        ->get(['id', 'name', 'pool_availability_notes', 'atasan_id']);

        $payload = $members->map(function ($member) {
            $roleLabel = optional($member->jabatan)->name
                ?? optional($member->roles->first())->name
                ?? 'Tidak ada jabatan';

            return [
                'id' => $member->id,
                'name' => $member->name,
                'role' => $roleLabel,
                'pool_availability_notes' => $member->pool_availability_notes,
                'atasan_id' => $member->atasan_id,
            ];
        });

        return response()->json($payload);
    }

    public function showWorkflow()
    {
        $this->authorize('viewAny', User::class);
        return view('resource_pool.workflow');
    }
}
