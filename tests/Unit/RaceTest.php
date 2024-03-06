<?php

namespace Tests\Feature;

use App\Models\Race;
use App\Models\User;
use Database\Seeders\InitialUser;
use Database\Seeders\Profiles;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class RaceTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_can_add_new_race(): void
    {
        $user = User::factory()->create(['profile_id' => 2, 'password' => '12345678']);

        $response = $this->actingAs($user)->post('/api/races', ['name' => 'Gato']);

        $response->assertStatus(201);
        $response->assertJson([
            'name' => 'Gato',
            'id' => true,
            'created_at' => true,
            'updated_at' => true
        ]);
    }


    public function test_cannot_create_with_invalid_name(): void
    {

        $user = User::factory()->create(['profile_id' => 2, 'password' => '12345678']);

        $response = $this->actingAs($user)->post('/api/races', ['name' => 1]);

        $response->assertStatus(400);
        $response->assertJson([
            "message" => "The name field must be a string.",
            "status" => 400,
            "errors" => [],
            "data" => []
        ]);
    }

    public function test_cannot_create_without_name(): void
    {
        $user = User::factory()->create(['profile_id' => 2, 'password' => '12345678']);

        $response = $this->actingAs($user)->post('/api/races', ['name' => '']);

        $response->assertStatus(400);
        $response->assertJson([
            "message" => "The name field is required.",
            "status" => 400,
            "errors" => [],
            "data" => []
        ]);
    }

    public function test_user_can_list_all_races()
    {
        // Race::factory(10)->create();
        Race::factory(5)->create();


        $user = User::factory()->create(['profile_id' => 2, 'password' => '12345678']);
        $response = $this->actingAs($user)->get('/api/races');

        $response->assertStatus(200)->assertJsonStructure([
            '*' => [
                'created_at',
                'updated_at',
                'name',
                'id'
            ]
        ]);
    }
}
