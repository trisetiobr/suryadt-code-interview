<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

use App\Models\User;

class UserControllerTest extends TestCase
{
    use RefreshDatabase;

    public function testStoreUser()
    {
        $data = [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'testingrandom@randomrandomrandom.com',
            'date_of_birth' => '1990-01-15',
            'location' => 'Africa/Abidjan',
        ];

        $response = $this->post('/api/user', $data);

        $response->assertStatus(201)
            ->assertJsonStructure(['id', 'first_name', 'last_name', 'date_of_birth', 'location']);

        $this->assertDatabaseHas('users', $data);
    }

    public function testStoreUserInvalidRequest()
    {
        $data = [
            'first_name' => '',
            'last_name' => '',
            'date_of_birth' => '',
            'location' => 'Some Random Timezone',
        ];

        $response = $this->post('/api/user', $data);

        $response->assertStatus(400);
    }

    public function testUpdateUser()
    {
        $user = User::factory()->create();

        $data = [
            'first_name' => 'UpdatedFirstName',
            'last_name' => 'UpdatedLastName',
            'date_of_birth' => '1995-05-10',
            'location' => 'Asia/Jakarta',
        ];

        $response = $this->put('/api/user', array_merge(['id' => $user->id], $data));

        $response->assertStatus(200)
            ->assertJson(['message' => 'User updated']);

        $this->assertDatabaseHas('users', $data);
    }

    public function testUpdateUserInvalidRequest()
    {
        $user = User::factory()->create();

        $data = [
            'first_name' => '',
            'last_name' => '',
            'date_of_birth' => '',
            'location' => 'Random Location',
        ];

        $response = $this->put('/api/user', array_merge(['id' => $user->id], $data));

        $response->assertStatus(400);
    }

    public function testDeleteUser()
    {
        $user = User::factory()->create();

        $response = $this->delete('/api/user', ['id' => $user->id]);

        $response->assertStatus(200)
            ->assertJson(['message' => 'User deleted']);

        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }

    public function testDeleteUserWhenNotExists()
    {
        $response = $this->delete('/api/user', ['id' => rand(1, 1000)]);

        $response->assertStatus(404)
            ->assertJson(['message' => 'User not found']);

    }
}
