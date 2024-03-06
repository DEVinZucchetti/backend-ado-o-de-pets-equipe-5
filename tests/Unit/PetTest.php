<?php

namespace Tests\Feature;

use App\Models\Pet;
use App\Models\Race;
use App\Models\Specie;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class PetTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_can_edit_one_pet(): void
    {
        $specie = Specie::factory()->create();
        $race = Race::factory()->create();
        $pet = Pet::factory()->create(['race_id' => $race->id, 'specie_id' => $specie->id]);

        $user = User::factory()->create(['profile_id' => 2, 'password' => '87120518']);

        $body = [
            'name' => 'Novo Nome',
            'size' => 'LARGE',
            'weight' => 12,
            'race_id' => $race->id,
            'specie_id' => $specie->id,
        ];

        $response = $this->actingAs($user)->put("/api/pets/$pet->id", $body);

        $this->assertDatabaseHas('pets', [
            'id' => $pet->id,
            'size' => $body['size'],
            'weight' => $body['weight'],
            'name' => $body['name'],
            'race_id' => $body['race_id'],
            'specie_id' => $body['specie_id'],
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'id' => true,
            'name' => $body['name'],
            'weight' => $body['weight'],
            'size' => $body['size'],
            'age' => true,
            'race_id' => $body['race_id'],
            'specie_id' => $body['specie_id'],
            'client_id' => null,
        ]);
    }

}
