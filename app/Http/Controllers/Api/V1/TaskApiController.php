<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Task;
use App\Traits\ApiResponser;
use Illuminate\Http\Request;

class TaskApiController extends Controller
{
    use ApiResponser;

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $tasks = Task::with(['project', 'users'])->paginate(20);
        return $this->successResponse($tasks, 'Tasks retrieved successfully.');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Task  $task
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Task $task)
    {
        $task->load('project', 'users', 'subTasks', 'comments', 'attachments');
        return $this->successResponse($task, 'Task retrieved successfully.');
    }
}
