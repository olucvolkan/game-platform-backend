<?php

namespace Tests\Feature;

use App\Models\Game;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class GameListTest extends TestCase
{
    use DatabaseTransactions;

    public function test_can_get_game_list(): void
    {
        Game::factory()->count(5)->create();

        $response = $this->getJson('/api/list');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
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
                    ],
                ],
                'meta' => [
                    'total',
                    'page',
                    'perPage',
                    'lastPage',
                ],
            ]);
    }

    public function test_can_paginate_game_list(): void
    {
        Game::factory()->count(25)->create();

        $response = $this->getJson('/api/list?page=1&perPage=10');

        $response->assertStatus(200)
            ->assertJsonPath('meta.page', 1)
            ->assertJsonPath('meta.perPage', 10)
            ->assertJsonCount(10, 'data');

        $response2 = $this->getJson('/api/list?page=2&perPage=10');
        $response2->assertStatus(200)
            ->assertJsonPath('meta.page', 2);
    }

    public function test_can_filter_by_platform(): void
    {
        Game::factory()->steam()->count(3)->create();
        Game::factory()->xbox()->count(2)->create();
        Game::factory()->playstation()->count(2)->create();

        $response = $this->getJson('/api/list?platforms=Steam');

        $response->assertStatus(200);

        $data = $response->json('data');
        foreach ($data as $game) {
            $this->assertEquals('Steam', $game['platform']);
        }
    }

    public function test_can_filter_by_multiple_platforms(): void
    {
        Game::factory()->steam()->count(3)->create();
        Game::factory()->xbox()->count(2)->create();
        Game::factory()->playstation()->count(2)->create();

        $response = $this->getJson('/api/list?platforms=Steam,Xbox');

        $response->assertStatus(200);

        $data = $response->json('data');
        foreach ($data as $game) {
            $this->assertContains($game['platform'], ['Steam', 'Xbox']);
        }
    }

    public function test_can_filter_by_product_type(): void
    {
        Game::factory()->count(3)->create(['product_type' => 'Game']);
        Game::factory()->dlc()->count(2)->create();

        $response = $this->getJson('/api/list?types=DLC');

        $response->assertStatus(200);

        $data = $response->json('data');
        foreach ($data as $game) {
            $this->assertEquals('DLC', $game['productType']);
        }
    }

    public function test_can_filter_by_price_range(): void
    {
        Game::factory()->create(['price' => 5.00]);
        Game::factory()->create(['price' => 15.00]);
        Game::factory()->create(['price' => 25.00]);
        Game::factory()->create(['price' => 50.00]);

        $response = $this->getJson('/api/list?minPrice=10&maxPrice=30');

        $response->assertStatus(200);

        $data = $response->json('data');
        foreach ($data as $game) {
            $this->assertGreaterThanOrEqual(10, $game['price']);
            $this->assertLessThanOrEqual(30, $game['price']);
        }
    }

    public function test_can_sort_by_price_asc(): void
    {
        Game::factory()->create(['price' => 50.00]);
        Game::factory()->create(['price' => 10.00]);
        Game::factory()->create(['price' => 30.00]);

        $response = $this->getJson('/api/list?sort=price-asc');

        $response->assertStatus(200);

        $data = $response->json('data');
        $prices = array_column($data, 'price');

        $sortedPrices = $prices;
        sort($sortedPrices);

        $this->assertEquals($sortedPrices, $prices);
    }

    public function test_can_sort_by_price_desc(): void
    {
        Game::factory()->create(['price' => 50.00]);
        Game::factory()->create(['price' => 10.00]);
        Game::factory()->create(['price' => 30.00]);

        $response = $this->getJson('/api/list?sort=price-desc');

        $response->assertStatus(200);

        $data = $response->json('data');
        $prices = array_column($data, 'price');

        $sortedPrices = $prices;
        rsort($sortedPrices);

        $this->assertEquals($sortedPrices, $prices);
    }

    public function test_can_sort_by_discount(): void
    {
        Game::factory()->create(['discount' => 10]);
        Game::factory()->create(['discount' => 50]);
        Game::factory()->create(['discount' => 30]);

        $response = $this->getJson('/api/list?sort=discount');

        $response->assertStatus(200);

        $data = $response->json('data');
        $discounts = array_column($data, 'discount');

        $sortedDiscounts = $discounts;
        rsort($sortedDiscounts);

        $this->assertEquals($sortedDiscounts, $discounts);
    }

    public function test_returns_empty_data_when_no_games(): void
    {
        $response = $this->getJson('/api/list');

        $response->assertStatus(200)
            ->assertJsonPath('meta.total', 0)
            ->assertJsonCount(0, 'data');
    }
}
