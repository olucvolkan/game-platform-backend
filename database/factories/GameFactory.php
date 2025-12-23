<?php

namespace Database\Factories;

use App\Models\Game;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Game>
 */
class GameFactory extends Factory
{
    protected $model = Game::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = fake()->unique()->words(rand(2, 4), true);
        $originalPrice = fake()->randomFloat(2, 9.99, 69.99);
        $discount = fake()->randomElement([0, 0, 0, 10, 15, 20, 25, 30, 50]);
        $price = $discount > 0 ? round($originalPrice * (1 - $discount / 100), 2) : $originalPrice;

        return [
            'igdb_id' => fake()->unique()->numberBetween(1000, 999999),
            'slug' => Str::slug($title),
            'title' => ucwords($title),
            'image' => 'https://images.igdb.com/igdb/image/upload/t_cover_big/' . fake()->lexify('????????') . '.jpg',
            'price' => $price,
            'original_price' => $originalPrice,
            'discount' => $discount,
            'platform' => fake()->randomElement(['PC', 'Steam', 'Xbox', 'PlayStation', 'Nintendo']),
            'region' => fake()->randomElement(['GLOBAL', 'EU', 'US', 'TR']),
            'product_type' => fake()->randomElement(['Game', 'DLC', 'Game Points', 'Subscription']),
            'has_cashback' => fake()->boolean(20),
            'cashback_percent' => fake()->randomElement([0, 5, 10]),
            'release_date' => fake()->dateTimeBetween('-5 years', 'now'),
            'developer' => fake()->company(),
            'publisher' => fake()->company(),
            'description' => fake()->paragraphs(3, true),
            'popularity_score' => fake()->numberBetween(1, 1000),
        ];
    }

    /**
     * State for Steam games.
     */
    public function steam(): static
    {
        return $this->state(fn (array $attributes) => [
            'platform' => 'Steam',
        ]);
    }

    /**
     * State for Xbox games.
     */
    public function xbox(): static
    {
        return $this->state(fn (array $attributes) => [
            'platform' => 'Xbox',
        ]);
    }

    /**
     * State for PlayStation games.
     */
    public function playstation(): static
    {
        return $this->state(fn (array $attributes) => [
            'platform' => 'PlayStation',
        ]);
    }

    /**
     * State for discounted games.
     */
    public function discounted(int $discount = 50): static
    {
        return $this->state(function (array $attributes) use ($discount) {
            $originalPrice = $attributes['original_price'];
            return [
                'discount' => $discount,
                'price' => round($originalPrice * (1 - $discount / 100), 2),
            ];
        });
    }

    /**
     * State for DLC products.
     */
    public function dlc(): static
    {
        return $this->state(fn (array $attributes) => [
            'product_type' => 'DLC',
        ]);
    }

    /**
     * State for cheap games (under $10).
     */
    public function cheap(): static
    {
        return $this->state(fn (array $attributes) => [
            'price' => fake()->randomFloat(2, 0.99, 9.99),
            'original_price' => fake()->randomFloat(2, 5.99, 19.99),
        ]);
    }
}
