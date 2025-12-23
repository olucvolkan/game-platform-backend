<?php

namespace Tests\Feature;

use App\Models\Game;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class FavoritesTest extends TestCase
{
    use DatabaseTransactions;

    private User $user;
    private string $token;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->token = $this->user->createToken('test-token')->plainTextToken;
    }

    public function test_authenticated_user_can_get_favorites(): void
    {
        $games = Game::factory()->count(3)->create();

        foreach ($games as $game) {
            $this->user->favorites()->attach($game->id);
        }

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->getJson('/api/favorites');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'slug',
                        'title',
                        'image',
                        'price',
                        'platform',
                    ],
                ],
            ])
            ->assertJsonCount(3, 'data');
    }

    public function test_unauthenticated_user_cannot_get_favorites(): void
    {
        $response = $this->getJson('/api/favorites');

        $response->assertStatus(401);
    }

    public function test_authenticated_user_can_add_game_to_favorites(): void
    {
        $game = Game::factory()->create();

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->postJson("/api/favorites/{$game->id}");

        $response->assertStatus(201)
            ->assertJson([
                'message' => 'Game added to favorites',
            ]);

        $this->assertTrue($this->user->favorites()->where('game_id', $game->id)->exists());
    }

    public function test_unauthenticated_user_cannot_add_favorites(): void
    {
        $game = Game::factory()->create();

        $response = $this->postJson("/api/favorites/{$game->id}");

        $response->assertStatus(401);
    }

    public function test_cannot_add_same_game_twice_to_favorites(): void
    {
        $game = Game::factory()->create();
        $this->user->favorites()->attach($game->id);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->postJson("/api/favorites/{$game->id}");

        $response->assertStatus(409)
            ->assertJson([
                'message' => 'Game is already in your favorites',
            ]);
    }

    public function test_cannot_add_non_existent_game_to_favorites(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->postJson('/api/favorites/99999');

        $response->assertStatus(404)
            ->assertJson([
                'message' => 'Game not found',
            ]);
    }

    public function test_authenticated_user_can_remove_game_from_favorites(): void
    {
        $game = Game::factory()->create();
        $this->user->favorites()->attach($game->id);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->deleteJson("/api/favorites/{$game->id}");

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Game removed from favorites',
            ]);

        $this->assertFalse($this->user->favorites()->where('game_id', $game->id)->exists());
    }

    public function test_unauthenticated_user_cannot_remove_favorites(): void
    {
        $game = Game::factory()->create();

        $response = $this->deleteJson("/api/favorites/{$game->id}");

        $response->assertStatus(401);
    }

    public function test_cannot_remove_game_not_in_favorites(): void
    {
        $game = Game::factory()->create();

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->deleteJson("/api/favorites/{$game->id}");

        $response->assertStatus(404)
            ->assertJson([
                'message' => 'Game is not in your favorites',
            ]);
    }

    public function test_favorites_are_ordered_by_added_date_desc(): void
    {
        $game1 = Game::factory()->create(['title' => 'First Game']);
        $game2 = Game::factory()->create(['title' => 'Second Game']);
        $game3 = Game::factory()->create(['title' => 'Third Game']);

        // Add in order with slight delay simulation
        $this->user->favorites()->attach($game1->id, ['created_at' => now()->subMinutes(3)]);
        $this->user->favorites()->attach($game2->id, ['created_at' => now()->subMinutes(2)]);
        $this->user->favorites()->attach($game3->id, ['created_at' => now()->subMinutes(1)]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->getJson('/api/favorites');

        $response->assertStatus(200);

        $data = $response->json('data');
        // Most recently added should be first
        $this->assertEquals('Third Game', $data[0]['title']);
        $this->assertEquals('Second Game', $data[1]['title']);
        $this->assertEquals('First Game', $data[2]['title']);
    }

    public function test_favorites_returns_empty_array_for_new_user(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->getJson('/api/favorites');

        $response->assertStatus(200)
            ->assertJsonCount(0, 'data');
    }
}
