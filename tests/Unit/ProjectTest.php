<?php

namespace Tests\Unit;

use App\Models\Project;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Tests\CreatesApplication;

class ProjectTest extends BaseTestCase
{
    use CreatesApplication, RefreshDatabase;

    /** @test */
    public function a_user_can_be_assigned_to_a_project()
    {
        $user = factory(User::class)->create();
        $project = factory(Project::class)->create([
            'user_id' => $user->id
        ]);

        $project->assignUsers(factory(User::class)->create());
        $project->refresh();

        $this->assertCount(2, $project->users);
    }

    /** @test */
    public function an_already_assinged_user_can_be_removed_from_the_project()
    {
        $user = factory(User::class)->create();
        $project = factory(Project::class)->create([
            'user_id' => $user->id
        ]);

        $project->assignUsers($newUser = factory(User::class)->create());
        $this->assertCount(2, $project->users);

        $project->assignUsers($newUser);
        $project->refresh();
        $this->assertCount(1, $project->users);
    }

    /** @test */
    public function only_previously_assigned_users_can_ba_removed_from_project()
    {
        $user = factory(User::class)->create();
        $project = factory(Project::class)->create([
            'user_id' => $user->id
        ]);

        $project->assignUsers(factory(User::class)->create());
        $this->assertCount(2, $project->users);

        $project->unAssignUsers(factory(User::class)->create());

        $project->refresh();
        $this->assertCount(2, $project->users);
    }
}
