<?php

namespace Tests\Feature;

use App\Models\Game;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class GameDetailTest extends TestCase
{
    use DatabaseTransactions;

    public function test_can_get_game_details_by_slug(): void
    {
        $game = Game::factory()->create([
            'slug' => 'test-game-slug',
            'title' => 'Test Game Title',
        ]);

        $response = $this->getJson('/api/games/test-game-slug');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'id',
                'slug',
                'title',
                'image',
                'price',
                'originalPrice',
                'discount',
                'platform',
                'region',
                'productType',
                'hasCashback',
                'cashbackPercent',
                'releaseDate',
                'developer',
                'publisher',
                'description',
                'genres',
                'screenshots',
            ])
            ->assertJsonPath('slug', 'test-game-slug')
            ->assertJsonPath('title', 'Test Game Title');
    }

    public function test_returns_404_for_non_existent_game(): void
    {
        $response = $this->getJson('/api/games/non-existent-game');

        $response->assertStatus(404)
            ->assertJson([
                'error' => 'Game not found',
                'statusCode' => 404,
            ]);
    }

    public function test_game_details_include_all_fields(): void
    {
        $game = Game::factory()->create([
            'slug' => 'complete-game',
            'title' => 'Complete Game',
            'price' => 29.99,
            'original_price' => 59.99,
            'discount' => 50,
            'platform' => 'Steam',
            'region' => 'GLOBAL',
            'product_type' => 'Game',
            'has_cashback' => true,
            'cashback_percent' => 5,
            'developer' => 'Test Developer',
            'publisher' => 'Test Publisher',
            'description' => 'A test game description.',
        ]);

        $response = $this->getJson('/api/games/complete-game');

        $response->assertStatus(200)
            ->assertJsonPath('price', 29.99)
            ->assertJsonPath('originalPrice', 59.99)
            ->assertJsonPath('discount', 50)
            ->assertJsonPath('platform', 'Steam')
            ->assertJsonPath('region', 'GLOBAL')
            ->assertJsonPath('productType', 'Game')
            ->assertJsonPath('hasCashback', true)
            ->assertJsonPath('cashbackPercent', 5)
            ->assertJsonPath('developer', 'Test Developer')
            ->assertJsonPath('publisher', 'Test Publisher');
    }

    public function test_game_details_returns_empty_arrays_for_missing_relations(): void
    {
        $game = Game::factory()->create(['slug' => 'game-no-relations']);

        $response = $this->getJson('/api/games/game-no-relations');

        $response->assertStatus(200)
            ->assertJsonPath('genres', [])
            ->assertJsonPath('screenshots', []);
    }
}
