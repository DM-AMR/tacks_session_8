<?php

use App\Http\Controllers\Api\TaskController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

/**
 * Task API Routes
 * 
 * Base URL: /api/tasks
 */
Route::prefix('tasks')->group(function () {
    // Get all tasks with filtering and pagination
    // GET /api/tasks
    // Query parameters:
    //   - status: filter by status (pending, in_progress, completed)
    //   - sort_by: sort field (default: created_at)
    //   - sort_order: asc or desc (default: desc)
    //   - per_page: items per page (default: 15)
    Route::get('/', [TaskController::class, 'index'])->name('tasks.index');

    // Get a specific task
    // GET /api/tasks/{id}
    Route::get('/{task}', [TaskController::class, 'show'])->name('tasks.show');

    // Create a new task
    // POST /api/tasks
    // Body: { "title": "...", "description": "...", "status": "...", "due_date": "..." }
    Route::post('/', [TaskController::class, 'store'])->name('tasks.store');

    // Update a task
    // PUT /api/tasks/{id}
    // Body: { "title": "...", "description": "...", "status": "...", "due_date": "..." }
    Route::put('/{task}', [TaskController::class, 'update'])->name('tasks.update');

    // Delete a task
    // DELETE /api/tasks/{id}
    Route::delete('/{task}', [TaskController::class, 'destroy'])->name('tasks.destroy');
});
