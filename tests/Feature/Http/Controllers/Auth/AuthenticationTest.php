<?php

namespace Tests\Feature\Http\Controllers\Auth;

use App\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use DatabaseMigrations;

    /** @test */
    public function a_user_can_login()
    {
        $user = factory(User::class)->create();

        $response = $this->json('POST', '/api/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertStatus(200);

        $response->assertJsonStructure([
            'data' => [
                'accessToken',
                'user' => [
                    'id', 'name', 'email'
                ]
            ]
        ]);
    }

    /** @test */
    public function an_authenticated_user_can_access_his_information()
    {
        $user = factory(User::class)->create();

        $response = $this->actingAs($user)->json('GET', '/api/user');

        $response->assertStatus(200);

        $response->assertJsonStructure([
            'data' => ['id', 'name', 'email']
        ]);
    }

    /** @test */
    public function an_unauthenticated_user_cannot_access_information()
    {
        $response = $this->json('GET', '/api/user');

        $response->assertStatus(401);
    }
}
