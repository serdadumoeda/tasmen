<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Traits\ApiResponser;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;

class ProjectApiController extends Controller
{
    use ApiResponser;

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $projects = QueryBuilder::for(Project::class)
            ->allowedFilters([
                AllowedFilter::exact('status'),
                AllowedFilter::exact('owner_id'),
            ])
            ->allowedSorts(['name', 'start_date', 'end_date'])
            ->paginate(20)
            ->appends(request()->query());

        return $this->successResponse($projects, 'Projects retrieved successfully.');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Project  $project
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Project $project)
    {
        $project->load('owner', 'team', 'tasks', 'budgetItems');
        return $this->successResponse($project, 'Project retrieved successfully.');
    }
}
