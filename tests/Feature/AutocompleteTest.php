<?php

namespace Tests\Feature;

use App\Models\Game;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class AutocompleteTest extends TestCase
{
    use DatabaseTransactions;

    public function test_can_get_autocomplete_suggestions(): void
    {
        Game::factory()->create(['title' => 'Grand Theft Auto V']);
        Game::factory()->create(['title' => 'Grand Theft Auto IV']);
        Game::factory()->create(['title' => 'Red Dead Redemption']);

        $response = $this->getJson('/api/autocomplete?q=grand');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'suggestions' => [
                    '*' => [
                        'id',
                        'title',
                        'slug',
                    ],
                ],
            ]);
    }

    public function test_autocomplete_requires_query_parameter(): void
    {
        $response = $this->getJson('/api/autocomplete');

        $response->assertStatus(400)
            ->assertJson([
                'error' => 'Invalid request parameters',
                'statusCode' => 400,
            ]);
    }

    public function test_autocomplete_respects_limit_parameter(): void
    {
        Game::factory()->count(15)->create(['title' => 'Test Game']);

        $response = $this->getJson('/api/autocomplete?q=test&limit=5');

        $response->assertStatus(200);

        $suggestions = $response->json('suggestions');
        $this->assertLessThanOrEqual(5, count($suggestions));
    }

    public function test_autocomplete_returns_empty_for_no_matches(): void
    {
        Game::factory()->create(['title' => 'Unrelated Game']);

        $response = $this->getJson('/api/autocomplete?q=zzzznonexistent');

        $response->assertStatus(200)
            ->assertJsonCount(0, 'suggestions');
    }

    public function test_autocomplete_is_case_insensitive(): void
    {
        Game::factory()->create(['title' => 'FIFA 2024']);

        $responseLower = $this->getJson('/api/autocomplete?q=fifa');
        $responseUpper = $this->getJson('/api/autocomplete?q=FIFA');

        $responseLower->assertStatus(200);
        $responseUpper->assertStatus(200);

        // Both should find the game
        $this->assertEquals(
            count($responseLower->json('suggestions')),
            count($responseUpper->json('suggestions'))
        );
    }

    public function test_autocomplete_default_limit_is_10(): void
    {
        Game::factory()->count(20)->create(['title' => 'Game Title']);

        $response = $this->getJson('/api/autocomplete?q=game');

        $response->assertStatus(200);

        $suggestions = $response->json('suggestions');
        $this->assertLessThanOrEqual(10, count($suggestions));
    }
}
