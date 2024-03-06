<?php

namespace Tests\Feature;

use App\Models\Pet;
use App\Models\Race;
use App\Models\Specie;
use App\Models\User;
use Tests\TestCase;

class SpecieTest extends TestCase
{
    /**
     * A basic feature test example.
     */

    public function test_user_can_list_all_species()
    {

        Specie::factory(15)->create();
        $this->assertDatabaseCount('species', 15);

        $user = User::factory()->create(['profile_id' => 2, 'password' => '12345678']);
        $response = $this->actingAs($user)->get('/api/species');

        $response->assertStatus(200)->assertJsonStructure([
            '*' => [
                'created_at',
                'updated_at',
                'name',
                'id'
            ]
        ]);
    }

    public function test_can_add_new_specie(): void
    {
        $user = User::factory()->create(['profile_id' => 2, 'password' => '12345678']);

        $response = $this->actingAs($user)->post('/api/species', ['name' => 'Caramelo']);

        $this->assertDatabaseCount('species', 1);
        $response->assertStatus(201);
        $response->assertJson([
            'name' => 'Caramelo',
            'id' => true,
            'created_at' => true,
            'updated_at' => true
        ]);
    }

    public function test_cannot_create_with_invalid_name(): void
    {

        $user = User::factory()->create(['profile_id' => 2, 'password' => '12345678']);

        $response = $this->actingAs($user)->post('/api/species', ['name' => 1]);

        $response->assertStatus(400);
        $response->assertJson([
            "message" => "The name field must be a string.",
            "status" => 400,
            "errors" => [],
            "data" => []
        ]);
    }


    public function test_use_can_delete_specie(): void
    {
        $specieCreated = Specie::factory()->create();

        $user = User::factory()->create(['profile_id' => 2, 'password' => '12345678']);

        $response = $this->actingAs($user)->delete("/api/species/$specieCreated->id");

        $this->assertDatabaseMissing('species', ['id' => $specieCreated->id]);
        $response->assertStatus(204);
    }

    public function test_use_can_delete_specie_with_many_species_in_database(): void
    {
        Specie::factory(10)->create();
        $specieCreated = Specie::factory()->create();

        $user = User::factory()->create(['profile_id' => 2, 'password' => '12345678']);
        $response = $this->actingAs($user)->delete("/api/species/$specieCreated->id");

        $this->assertDatabaseCount('species', 10);
        $this->assertDatabaseMissing('species', ['id' => $specieCreated->id]);
        $response->assertStatus(204);
    }

    public function test_use_can_delete_specie_with_pet(): void
    {
        $specie = Specie::factory()->create();
        $race = Race::factory()->create();
        $pet  = Pet::factory()->create(['race_id' => $race->id, 'specie_id' => $specie->id]);

        $user = User::factory()->create(['profile_id' => 2, 'password' => '12345678']);
        $response = $this->actingAs($user)->delete("/api/species/$specie->id");

        $response->assertStatus(409);
        $response->assertJson([
            'status' => 409,
            'message' => 'Existem pets usando essa espécie',
            'errors' => [],
            'data' => []
        ]);
        $this->assertDatabaseHas('species', ['id' =>  $specie->id]);
    }
}
