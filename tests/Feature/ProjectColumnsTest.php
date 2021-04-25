<?php

namespace Tests\Feature;

use App\Models\Column;
use App\Models\Project;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ProjectColumnsTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function a_user_can_create_a_column_in_his_project()
    {
        $user = factory(User::class)->create();
        $project = factory(Project::class)->create([
            'user_id' => $user->id
        ]);

        Sanctum::actingAs($user);

        $response = $this->json('POST', "/api/projects/{$project->id}/columns", [
            'title' => 'First column'
        ]);

        $response->assertCreated();

        $response->assertJsonStructure([
            'data' => [
                'id', 'title'
            ]
        ]);

        $response->assertJson([
            'data' => [
                'title' => 'First column',
            ]
        ]);

        $project->refresh();

        $this->assertEquals(1, $project->columns->count());
        $this->assertEquals('First column', $project->columns->first()->title);
    }

    /** @test */
    public function a_title_is_required_while_creating_a_column()
    {
        $user = factory(User::class)->create();
        $project = factory(Project::class)->create([
            'user_id' => $user->id
        ]);

        Sanctum::actingAs($user);

        $response = $this->json('POST', "/api/projects/{$project->id}/columns", [
            'title' => ''
        ]);

        $response->assertJsonValidationErrors('title');

        $project->refresh();

        $this->assertEquals(0, $project->columns->count());
    }

    /** @test */
    public function a_column_can_be_updated()
    {
        $user = factory(User::class)->create();
        $project = factory(Project::class)->create([
            'user_id' => $user->id
        ]);
        $column = factory(Column::class)->create([
            'project_id' => $project->id
        ]);

        Sanctum::actingAs($user);

        $response = $this->json('PATCH', "/api/projects/{$project->id}/columns/{$column->id}", [
            'title' => 'Updated title'
        ]);

        $response->assertOk();
        $this->assertEquals('Updated title', $project->columns->first()->title);

        $response->assertJsonStructure([
            'data' => [
                'id', 'title'
            ]
        ]);

        $response->assertJson([
            'data' => [
                'title' => 'Updated title',
            ]
        ]);
    }

    /** @test */
    public function a_column_belonging_to_different_project_cannot_be_updated()
    {
        $user = factory(User::class)->create();
        $project = factory(Project::class)->create([
            'user_id' => $user->id
        ]);
        factory(Column::class)->create([
            'project_id' => $project->id
        ]);

        $secondProject = factory(Project::class)->create([
            'user_id' => $user->id
        ]);
        $columnForSecondProject = factory(Column::class)->create([
            'project_id' => $secondProject->id
        ]);

        Sanctum::actingAs($user);

        $response = $this->json('PATCH', "/api/projects/{$project->id}/columns/{$columnForSecondProject->id}", [
            'title' => 'Updated title'
        ]);

        $response->assertNotFound();

        $project->refresh();
        $this->assertCount(1, $project->columns);
    }

    /** @test */
    public function a_column_can_be_deleted()
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

        $response = $this->json('DELETE', "/api/projects/{$project->id}/columns/{$column->id}");

        $response->assertOk();
        $response->assertJsonStructure([
            'message'
        ]);
        $this->assertCount(0, $project->refresh()->columns);
    }
}
