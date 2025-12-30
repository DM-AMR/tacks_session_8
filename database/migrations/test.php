<?php

namespace Tests\Feature;

use App\Models\Task;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test getting all tasks
     */
    public function test_index_returns_all_tasks(): void
    {
        // Create sample tasks
        Task::factory()->count(3)->create();

        $response = $this->getJson('/api/tasks');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'title',
                        'description',
                        'status',
                        'due_date',
                        'created_at',
                        'updated_at',
                    ]
                ],
                'links',
                'meta'
            ]);
    }

    /**
     * Test filtering tasks by status
     */
    public function test_index_filters_by_status(): void
    {
        Task::factory()->create(['status' => 'pending']);
        Task::factory()->create(['status' => 'in_progress']);
        Task::factory()->create(['status' => 'completed']);

        $response = $this->getJson('/api/tasks?status=pending');

        $response->assertStatus(200);
        $this->assertEquals(1, count($response->json('data')));
        $this->assertEquals('pending', $response.json('data.0.status'));
    }

    /**
     * Test getting a single task
     */
    public function test_show_returns_single_task(): void
    {
        $task = Task::factory()->create([
            'title' => 'Test Task',
            'description' => 'Test Description',
        ]);

        $response = $this->getJson("/api/tasks/{$task->id}");

        $response->assertStatus(200)
            ->assertJsonPath('data.id', $task->id)
            ->assertJsonPath('data.title', 'Test Task')
            ->assertJsonPath('data.description', 'Test Description');
    }

    /**
     * Test showing non-existent task returns 404
     */
    public function test_show_returns_404_for_non_existent_task(): void
    {
        $response = $this->getJson('/api/tasks/999');

        $response->assertStatus(404);
    }

    /**
     * Test creating a task with valid data
     */
    public function test_store_creates_task_with_valid_data(): void
    {
        $taskData = [
            'title' => 'New Task',
            'description' => 'Task Description',
            'status' => 'pending',
            'due_date' => '2024-12-31 23:59:59',
        ];

        $response = $this->postJson('/api/tasks', $taskData);

        $response->assertStatus(201)
            ->assertJsonPath('message', 'Task created successfully')
            ->assertJsonPath('data.title', 'New Task');

        $this->assertDatabaseHas('tasks', [
            'title' => 'New Task',
            'description' => 'Task Description',
        ]);
    }

    /**
     * Test creating a task without title returns validation error
     */
    public function test_store_returns_validation_error_without_title(): void
    {
        $taskData = [
            'description' => 'Task Description',
        ];

        $response = $this->postJson('/api/tasks', $taskData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['title']);
    }

    /**
     * Test updating a task
     */
    public function test_update_modifies_task(): void
    {
        $task = Task::factory()->create([
            'title' => 'Original Title',
            'status' => 'pending',
        ]);

        $updateData = [
            'title' => 'Updated Title',
            'status' => 'in_progress',
        ];

        $response = $this->putJson("/api/tasks/{$task->id}", $updateData);

        $response->assertStatus(200)
            ->assertJsonPath('message', 'Task updated successfully')
            ->assertJsonPath('data.title', 'Updated Title')
            ->assertJsonPath('data.status', 'in_progress');

        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'title' => 'Updated Title',
            'status' => 'in_progress',
        ]);
    }

    /**
     * Test updating non-existent task returns 404
     */
    public function test_update_returns_404_for_non_existent_task(): void
    {
        $updateData = ['title' => 'Updated Title'];

        $response = $this->putJson('/api/tasks/999', $updateData);

        $response->assertStatus(404);
    }

    /**
     * Test deleting a task
     */
    public function test_destroy_deletes_task(): void
    {
        $task = Task::factory()->create();

        $response = $this->deleteJson("/api/tasks/{$task->id}");

        $response->assertStatus(200)
            ->assertJsonPath('message', 'Task deleted successfully');

        $this->assertDatabaseMissing('tasks', ['id' => $task->id]);
    }

    /**
     * Test deleting non-existent task returns 404
     */
    public function test_destroy_returns_404_for_non_existent_task(): void
    {
        $response = $this->deleteJson('/api/tasks/999');

        $response->assertStatus(404);
    }

    /**
     * Test sorting tasks
     */
    public function test_index_sorts_by_specified_field(): void
    {
        Task::factory()->create(['title' => 'A Task']);
        Task::factory()->create(['title' => 'B Task']);
        Task::factory()->create(['title' => 'C Task']);

        $response = $this->getJson('/api/tasks?sort_by=title&sort_order=asc');

        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertEquals('A Task', $data[0]['title']);
        $this->assertEquals('B Task', $data[1]['title']);
        $this->assertEquals('C Task', $data[2]['title']);
    }

    /**
     * Test pagination
     */
    public function test_index_paginates_results(): void
    {
        Task::factory()->count(20)->create();

        $response = $this->getJson('/api/tasks?per_page=5');

        $response->assertStatus(200);
        $this->assertCount(5, $response->json('data'));
        $this->assertEquals(1, $response->json('meta.current_page'));
        $this->assertEquals(4, $response->json('meta.last_page'));
    }
}
