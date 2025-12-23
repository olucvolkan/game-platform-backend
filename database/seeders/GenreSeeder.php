<?php

namespace Database\Seeders;

use App\Models\Genre;
use Illuminate\Database\Seeder;

class GenreSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $genres = [
            ['name' => 'Action', 'slug' => 'action'],
            ['name' => 'RPG', 'slug' => 'rpg'],
            ['name' => 'Shooter', 'slug' => 'shooter'],
            ['name' => 'Strategy', 'slug' => 'strategy'],
            ['name' => 'Simulation', 'slug' => 'simulation'],
            ['name' => 'Sports', 'slug' => 'sports'],
            ['name' => 'Racing', 'slug' => 'racing'],
            ['name' => 'Puzzle', 'slug' => 'puzzle'],
            ['name' => 'Horror', 'slug' => 'horror'],
            ['name' => 'Sandbox', 'slug' => 'sandbox'],
            ['name' => 'Survival', 'slug' => 'survival'],
            ['name' => 'Adventure', 'slug' => 'adventure'],
            ['name' => 'Fighting', 'slug' => 'fighting'],
            ['name' => 'Platformer', 'slug' => 'platformer'],
            ['name' => 'Indie', 'slug' => 'indie'],
            ['name' => 'Open World', 'slug' => 'open-world'],
            ['name' => 'Multiplayer', 'slug' => 'multiplayer'],
            ['name' => 'MMO', 'slug' => 'mmo'],
        ];

        foreach ($genres as $genre) {
            Genre::create($genre);
        }
    }
}
