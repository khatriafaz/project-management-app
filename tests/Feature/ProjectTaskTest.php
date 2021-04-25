<?php

namespace Tests\Feature;

use App\Models\Column;
use App\Models\Project;
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
        $this->withoutExceptionHandling();

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
}
