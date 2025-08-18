<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Traits\ApiResponser;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;

class UserApiController extends Controller
{
    use ApiResponser;

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $users = QueryBuilder::for(User::class)
            ->allowedFilters([
                AllowedFilter::exact('status'),
                AllowedFilter::exact('unit_id'),
                AllowedFilter::exact('role'),
            ])
            ->allowedSorts(['name', 'email', 'created_at'])
            ->with('unit')
            ->paginate(20)
            ->appends(request()->query());

        return $this->successResponse($users, 'Users retrieved successfully.');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(User $user)
    {
        $user->load('unit', 'jabatan', 'atasan');
        return $this->successResponse($user, 'User retrieved successfully.');
    }
}
