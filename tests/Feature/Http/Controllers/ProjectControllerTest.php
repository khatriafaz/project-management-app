<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\Project;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ProjectControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function a_user_can_create_a_project()
    {
        $this->withoutExceptionHandling();

        $user = factory(User::class)->create();
        $project = factory(Project::class)->make();

        $response = $this->actingAs($user)->json('POST', '/api/projects', $project->toArray());

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
    public function a_user_can_update_a_project()
    {
        $user = factory(User::class)->create();
        $project = factory(Project::class)->create([
            'user_id' => $user->id
        ]);

        $response = $this->actingAs($user)->json('PUT', "/api/projects/{$project->id}", [
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

        $response = $this->actingAs($user)->json('DELETE', "/api/projects/{$project->id}");

        $response->assertStatus(204);

        $this->assertDatabaseMissing('projects', [
            'title' => $project->title,
            'description' => $project->description,
            'user_id' => $project->user_id
        ]);
    }
}
