<?php

namespace Tests\Feature;

use App\Models\Column;
use App\Models\Project;
use App\Models\Task;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ProjectTaskTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function a_task_can_be_added_to_a_project()
    {
        $user = factory(User::class)->create();
        $project = factory(Project::class)->create([
            'user_id' => $user->id
        ]);

        Sanctum::actingAs($user);

        $response = $this->json('POST', "/api/projects/{$project->id}/tasks", [
            'title' => 'First task',
            'description' => 'Task description',
        ]);

        $response->assertCreated();

        $response->assertJsonStructure([
            'data' => [
                'id', 'title', 'description'
            ]
        ]);

        $response->assertJson([
            'data' => [
                'title' => 'First task',
                'description' => 'Task description',
            ]
        ]);
    }

    /** @test */
    public function a_task_can_be_added_to_a_project_column()
    {
        $user = factory(User::class)->create();
        $project = factory(Project::class)->create([
            'user_id' => $user->id
        ]);
        $column = factory(Column::class)->create([
            'project_id' => $project->id
        ]);

        Sanctum::actingAs($user);

        $response = $this->json('POST', "/api/projects/{$project->id}/tasks", [
            'title' => 'First task',
            'description' => 'Task description',
            'column_id' => $column->id
        ]);

        $response->assertCreated();

        $response->assertJsonStructure([
            'data' => [
                'id', 'title', 'description'
            ]
        ]);

        $response->assertJson([
            'data' => [
                'title' => 'First task',
                'description' => 'Task description',
            ]
        ]);

        $column->refresh();

        $this->assertCount(1, $column->tasks);
    }

    /** @test */
    public function a_title_is_required_while_creating_a_task()
    {
        $user = factory(User::class)->create();
        $project = factory(Project::class)->create([
            'user_id' => $user->id
        ]);

        Sanctum::actingAs($user);

        $response = $this->json('POST', "/api/projects/{$project->id}/tasks", [
            'title' => '',
        ]);

        $response->assertJsonValidationErrors('title');
        $this->assertCount(0, $project->tasks);
    }

    /** @test */
    public function a_task_can_be_updated()
    {
        $user = factory(User::class)->create();
        $project = factory(Project::class)->create([
            'user_id' => $user->id
        ]);
        $task = factory(Task::class)->create([
            'project_id' => $project->id
        ]);

        Sanctum::actingAs($user);

        $response = $this->json('PUT', "/api/projects/{$project->id}/tasks/{$task->id}", [
            'title' => 'Updated task',
            'description' => 'Updated task description'
        ]);

        $response->assertOk();
        $response->assertJsonStructure([
            'data' => [
                'id', 'title', 'description'
            ]
        ]);

        $response->assertJson([
            'data' => [
                'title' => 'Updated task',
                'description' => 'Updated task description'
            ]
        ]);

        $task->refresh();

        $this->assertEquals('Updated task', $task->title);
        $this->assertEquals('Updated task description', $task->description);
        $this->assertCount(1, $project->refresh()->tasks);
    }

    /** @test */
    public function all_tasks_for_project_can_be_retrieved()
    {
        $user = factory(User::class)->create();
        $project = factory(Project::class)->create([
            'user_id' => $user->id
        ]);
        $tasks = factory(Task::class, 10)->create([
            'project_id' => $project->id
        ]);

        Sanctum::actingAs($user);

        $response = $this->json('GET', "/api/projects/{$project->id}/tasks");

        $response->assertOk();

        $response->assertJson([
            'data' => $tasks->map(function($task) {
                return [
                    'id' => $task->id,
                    'title' => $task->title,
                    'description' => $task->description
                ];
            })->toArray()
        ]);
    }

    /** @test */
    public function a_task_can_be_retrieved()
    {
        $user = factory(User::class)->create();
        $project = factory(Project::class)->create([
            'user_id' => $user->id
        ]);
        $task = factory(Task::class)->create([
            'project_id' => $project->id
        ]);

        Sanctum::actingAs($user);

        $response = $this->json('GET', "/api/projects/{$project->id}/tasks/{$task->id}");

        $response->assertOk();

        $response->assertJson([
            'data' => [
                'id' => $task->id,
                'title' => $task->title,
                'description' => $task->description
            ]
        ]);
    }

    /** @test */
    public function a_task_can_be_deleted()
    {
        $user = factory(User::class)->create();
        $project = factory(Project::class)->create([
            'user_id' => $user->id
        ]);
        $task = factory(Task::class)->create([
            'project_id' => $project->id
        ]);

        Sanctum::actingAs($user);

        $response = $this->json('DELETE', "/api/projects/{$project->id}/tasks/{$task->id}");

        $response->assertOk();
        $response->assertJsonStructure([
            'message'
        ]);

        $this->assertCount(0, Task::all());
    }

    /** @test */
    public function a_user_can_be_assigned_to_a_task()
    {
        $user = factory(User::class)->create();
        $project = factory(Project::class)->create([
            'user_id' => $user->id
        ]);
        $task = factory(Task::class)->create([
            'project_id' => $project->id
        ]);
        $assignedUser = factory(User::class)->create();

        Sanctum::actingAs($user);

        $response = $this->json('POST', "/api/projects/{$project->id}/tasks/{$task->id}/assign/{$assignedUser->id}");

        $response->assertOk();

        $task->refresh()->load('users');

        $this->assertCount(1, $task->users);
        $this->assertSame($assignedUser->id, $task->users->first()->id);
    }

    /** @test */
    public function a_user_can_be_unassigned_from_a_task()
    {
        $this->withoutExceptionHandling();

        $user = factory(User::class)->create();
        $project = factory(Project::class)->create([
            'user_id' => $user->id
        ]);
        $task = factory(Task::class)->create([
            'project_id' => $project->id
        ]);

        $assignedUser = factory(User::class)->create();
        $task->users()->attach($assignedUser);

        Sanctum::actingAs($user);

        $response = $this->json('POST', "/api/projects/{$project->id}/tasks/{$task->id}/unassign/{$assignedUser->id}");

        $response->assertOk();

        $task->refresh()->load('users');

        $this->assertCount(0, $task->users);
    }
}
