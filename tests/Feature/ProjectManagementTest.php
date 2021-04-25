<?php

namespace Tests\Feature;

use App\Models\Column;
use App\Models\Project;
use App\Models\Task;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ProjectManagementTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function a_user_can_create_a_project()
    {
        $this->withoutExceptionHandling();

        $user = factory(User::class)->create();
        $project = factory(Project::class)->make();

        Sanctum::actingAs($user);

        $response = $this->json('POST', '/api/projects', $project->toArray());

        $response->assertStatus(201);

        $response->assertJsonStructure([
            'data' => [
                'id', 'title', 'description', 'created_at', 'updated_at'
            ]
        ]);

        $response->assertJson([
            'data' => [
                'title' => $project->title,
                'description' => $project->description,
            ]
        ]);
    }

    /** @test */
    public function a_title_is_required_while_creating_a_project()
    {
        $user = factory(User::class)->create();

        Sanctum::actingAs($user);

        $response = $this->json('POST', '/api/projects', [
            'title' => ''
        ]);

        $response->assertJsonValidationErrors('title');

        $this->assertCount(0, Project::all());
    }

    /** @test */
    public function a_user_can_retrieve_a_project()
    {
        $user = factory(User::class)->create();
        $project = factory(Project::class)->create([
            'user_id' => $user->id
        ]);

        Sanctum::actingAs($user);

        $response = $this->json('GET', "/api/projects/{$project->id}");

        $response->assertStatus(200);

        $response->assertJson([
            'data' => [
                'title' => $project->title,
                'description' => $project->description,
            ]
        ]);
    }

    /** @test */
    public function a_user_can_retrieve_a_project_with_columns()
    {
        $user = factory(User::class)->create();
        $project = factory(Project::class)->create([
            'user_id' => $user->id
        ]);

        $columns = factory(Column::class, 5)->create([
            'project_id' => $project->id,
        ]);

        Sanctum::actingAs($user);

        $response = $this->json('GET', "/api/projects/{$project->id}", [
            'with' => ['columns']
        ]);

        $response->assertStatus(200);

        $response->assertJson([
            'data' => [
                'title' => $project->title,
                'description' => $project->description,
                'columns' => $columns->map(function ($column) {
                    return [
                        'id' => $column->id,
                        'title' => $column->title
                    ];
                })->toArray()
            ]
        ]);
    }

    /** @test */
    public function a_user_can_update_a_project()
    {
        $user = factory(User::class)->create();
        $project = factory(Project::class)->create([
            'user_id' => $user->id
        ]);

        Sanctum::actingAs($user);

        $response = $this->json('PUT', "/api/projects/{$project->id}", [
            'title' => $project->title . '_updated',
            'description' => $project->description . '_updated',
        ]);

        $response->assertStatus(200);

        $response->assertJsonStructure([
            'data' => [
                'id', 'title', 'description', 'created_at', 'updated_at'
            ]
        ]);

        $response->assertJson([
            'data' => [
                'title' => $project->title . '_updated',
                'description' => $project->description . '_updated',
            ]
        ]);
    }

    /** @test */
    public function a_user_can_delete_his_project()
    {
        $user = factory(User::class)->create();

        $project = factory(Project::class)->create([
            'user_id' => $user->id
        ]);

        Sanctum::actingAs($user);

        $response = $this->json('DELETE', "/api/projects/{$project->id}");

        $response->assertOk();
        $response->assertJsonStructure([
            'message'
        ]);

        $this->assertCount(0, Project::all());
    }

    /** @test */
    public function a_project_with_columns_can_be_deleted()
    {
        $user = factory(User::class)->create();

        $project = factory(Project::class)->create([
            'user_id' => $user->id
        ]);

        factory(Column::class, 10)->create([
            'project_id' => $project->id
        ]);

        Sanctum::actingAs($user);

        $response = $this->json('DELETE', "/api/projects/{$project->id}");

        $response->assertOk();
        $response->assertJsonStructure([
            'message'
        ]);

        $this->assertCount(0, Project::all());
        $this->assertCount(0, $project->columns);
    }

    /** @test */
    public function a_project_with_tasks_can_be_deleted()
    {
        $user = factory(User::class)->create();

        $project = factory(Project::class)->create([
            'user_id' => $user->id
        ]);

        factory(Task::class, 10)->create([
            'project_id' => $project->id
        ]);

        Sanctum::actingAs($user);

        $response = $this->json('DELETE', "/api/projects/{$project->id}");

        $response->assertOk();
        $response->assertJsonStructure([
            'message'
        ]);

        $this->assertCount(0, Project::all());
        $this->assertCount(0, $project->tasks);
    }

    /** @test */
    public function a_user_can_get_a_list_of_projects()
    {
        $user = factory(User::class)->create();

        $projects = factory(Project::class, 10)->create([
            'user_id' => $user->id
        ]);

        Sanctum::actingAs($user);

        $response = $this->json('GET', '/api/projects');

        $response->assertStatus(200);

        $response->assertJson([
            'data' => $projects->map(function ($project) {
                return [
                    'title' => $project->title,
                    'description' => $project->description,
                ];
            })->toArray()
        ]);
    }

    /** @test */
    public function a_user_can_get_a_list_of_projects_with_relations()
    {
        $user = factory(User::class)->create();

        $projects = factory(Project::class, 10)->create([
            'user_id' => $user->id
        ]);

        Sanctum::actingAs($user);

        $response = $this->json('GET', '/api/projects', [
            'with' => ['user']
        ]);

        $response->assertStatus(200);

        $response->assertJson([
            'data' => $projects->map(function ($project) use ($user) {
                return [
                    'title' => $project->title,
                    'description' => $project->description,
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                    ]
                ];
            })->toArray()
        ]);
    }

    /** @test */
    public function a_user_can_only_get_project_he_is_assigned_to()
    {
        $owner = factory(User::class)->create();
        $otherUser = factory(User::class)->create();

        $projects = factory(Project::class, 10)->create([
            'user_id' => $otherUser->id
        ]);

        Sanctum::actingAs($owner);

        $response = $this->json('GET', '/api/projects');

        $response->assertStatus(200);

        $response->assertJsonMissing([
            'data' => $projects->map(function ($project) {
                return [
                    'title' => $project->title,
                    'description' => $project->description,
                    'user_id' => $project->user_id,
                ];
            })->toArray()
        ]);
    }

    /** @test */
    public function a_user_can_be_assigned_to_a_project()
    {
        $owner = factory(User::class)->create();
        $otherUser = factory(User::class)->create();

        $project = factory(Project::class)->create([
            'user_id' => $owner->id
        ]);

        Sanctum::actingAs($owner);

        $response = $this->json('PUT', "/api/projects/{$project->id}", [
            'users' => $otherUser->id
        ]);

        $response->assertStatus(200);

        $response->assertJson([
            'data' => [
                'title' => $project->title,
                'description' => $project->description,
            ]
        ]);

        $project->refresh();

        $this->assertTrue($project->users()->count() === 2);
    }

    /** @test */
    public function an_already_assigned_user_can_be_unassinged_from_a_project()
    {
        $owner = factory(User::class)->create();
        $otherUser = factory(User::class)->create();

        $project = factory(Project::class)->create([
            'user_id' => $owner->id
        ]);

        $project->assignUsers($otherUser);

        Sanctum::actingAs($owner);

        $response = $this->json('PUT', "/api/projects/{$project->id}/un-assign", [
            'users' => $otherUser->id
        ]);

        $response->assertStatus(200);

        $response->assertJson([
            'data' => [
                'title' => $project->title,
                'description' => $project->description,
            ]
        ]);

        $project->refresh();

        $this->assertCount(1, $project->users);
    }

    /** @test */
    public function a_projects_columns_can_be_reordered()
    {
        $this->withoutExceptionHandling();

        $user = factory(User::class)->create();
        $project = factory(Project::class)->create([
            'user_id' => $user->id
        ]);
        $columns = factory(Column::class, 10)->create([
            'project_id' => $project->id
        ]);

        $newOrder = $columns->shuffle();

        Sanctum::actingAs($user);

        $response = $this->json('PUT', "/api/projects/{$project->id}/order-columns", [
            'columnIds' => $newOrder->map(function ($column) {
                return $column->id;
            })->toArray(),
            'with' => 'columns'
        ]);

        $response->assertStatus(200);

        $response->assertJson([
            'data' => [
                'title' => $project->title,
                'description' => $project->description,
                'columns' => $newOrder->map(function ($column) {
                    return [
                        'title' => $column->title
                    ];
                })->toArray()
            ]
        ]);

        $this->json('GET', "/api/projects/{$project->id}", [
            'with' => 'columns'
        ]);

        $response->assertStatus(200);

        $response->assertJson([
            'data' => [
                'title' => $project->title,
                'description' => $project->description,
                'columns' => $newOrder->map(function ($column) {
                    return [
                        'title' => $column->title
                    ];
                })->toArray()
            ]
        ]);
    }
}
