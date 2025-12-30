<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\TaskResource;
use App\Models\Task;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class TaskController extends Controller
{
    /**
     * Display a listing of all tasks.
     *
     * @param Request $request
     * @return AnonymousResourceCollection
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        // Get query parameters for filtering and sorting
        $status = $request->query('status');
        $sortBy = $request->query('sort_by', 'created_at');
        $sortOrder = $request->query('sort_order', 'desc');
        $perPage = $request->query('per_page', 15);

        // Build the query
        $query = Task::query();

        // Filter by status if provided
        if ($status) {
            $query->where('status', $status);
        }

        // Sort the results
        $query->orderBy($sortBy, $sortOrder);

        // Paginate the results
        $tasks = $query->paginate($perPage);

        return TaskResource::collection($tasks);
    }

    /**
     * Display the specified task.
     *
     * @param Task $task
     * @return TaskResource
     */
    public function show(Task $task): TaskResource
    {
        return new TaskResource($task);
    }

    /**
     * Store a newly created task in storage.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        // Validate the incoming request
        $validated = $request->validate(Task::createRules());

        // Create the task
        $task = Task::create($validated);

        return response()->json([
            'message' => 'Task created successfully',
            'data' => new TaskResource($task),
        ], 201);
    }

    /**
     * Update the specified task in storage.
     *
     * @param Request $request
     * @param Task $task
     * @return JsonResponse
     */
    public function update(Request $request, Task $task): JsonResponse
    {
        // Validate the incoming request
        $validated = $request->validate(Task::updateRules());

        // Update the task
        $task->update($validated);

        return response()->json([
            'message' => 'Task updated successfully',
            'data' => new TaskResource($task),
        ], 200);
    }

    /**
     * Remove the specified task from storage.
     *
     * @param Task $task
     * @return JsonResponse
     */
    public function destroy(Task $task): JsonResponse
    {
        $task->delete();

        return response()->json([
            'message' => 'Task deleted successfully',
        ], 200);
    }
}
