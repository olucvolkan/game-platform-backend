# Task: Laravel API Implementation

## Status
- Created: 2024-12-23 14:45
- Started: 2024-12-23 15:00
- Completed: 2024-12-23 15:45

## Description
Implement the Laravel 12 API backend for the Eneba clone based on the API contract defined in `api/endpoints.md`. The backend must exactly match the frontend expectations.

### Endpoints Implemented
1. `GET /api/list` - Paginated game list with filtering and fuzzy search
2. `GET /api/autocomplete` - Search autocomplete suggestions
3. `GET /api/games/{slug}` - Game details

### Technical Requirements (All Met)
- [x] Laravel 12 with PHP 8.2+
- [x] SQLite database (development)
- [x] TNTSearch for fuzzy search
- [x] Model-Controller-Service architecture
- [x] API Resources for response transformation
- [x] Database seeder with 99 games

## Files Created

### Migrations
- `/database/migrations/2024_12_23_000001_create_genres_table.php`
- `/database/migrations/2024_12_23_000002_create_games_table.php`
- `/database/migrations/2024_12_23_000003_create_game_genre_table.php`
- `/database/migrations/2024_12_23_000004_create_game_screenshots_table.php`

### Models
- `/app/Models/Game.php` - With Scout searchable trait and scopes
- `/app/Models/Genre.php` - Genre model with games relationship
- `/app/Models/GameScreenshot.php` - Screenshot model

### Controllers
- `/app/Http/Controllers/Api/GameListController.php` - Paginated list with filters
- `/app/Http/Controllers/Api/AutocompleteController.php` - Search suggestions
- `/app/Http/Controllers/Api/GameController.php` - Single game details

### Services
- `/app/Services/GameService.php` - Business logic for games
- `/app/Services/SearchService.php` - TNTSearch + fallback fuzzy search

### Resources
- `/app/Http/Resources/GameResource.php` - List item transformation
- `/app/Http/Resources/GameDetailResource.php` - Full game details
- `/app/Http/Resources/AutocompleteSuggestionResource.php` - Autocomplete items
- `/app/Http/Resources/PaginatedGameCollection.php` - Custom pagination

### Requests
- `/app/Http/Requests/GameListRequest.php` - List validation
- `/app/Http/Requests/AutocompleteRequest.php` - Autocomplete validation

### Seeders
- `/database/seeders/GenreSeeder.php` - 18 genres
- `/database/seeders/GameSeeder.php` - 99 games with real data

### Configuration
- `/routes/api.php` - API routes
- `/config/cors.php` - CORS for localhost:3000
- `/config/scout.php` - TNTSearch configuration

## API Response Examples

### GET /api/list
```json
{
  "data": [
    {
      "id": 1,
      "slug": "elden-ring",
      "title": "Elden Ring",
      "image": "https://images.igdb.com/...",
      "price": 44.99,
      "originalPrice": 59.99,
      "discount": 25,
      "platform": "Steam",
      "region": "GLOBAL",
      "productType": "Game",
      "hasCashback": true,
      "cashbackPercent": 10,
      "releaseDate": "2022-02-25",
      "developer": "FromSoftware",
      "publisher": "Bandai Namco Entertainment",
      "genres": ["Action", "RPG", "Open World"]
    }
  ],
  "meta": {
    "total": 99,
    "page": 1,
    "perPage": 20,
    "lastPage": 5
  }
}
```

### GET /api/autocomplete?q=mine
```json
{
  "suggestions": [
    {
      "id": 6,
      "title": "Minecraft",
      "slug": "minecraft",
      "image": "https://images.igdb.com/..."
    }
  ]
}
```

### GET /api/games/minecraft
```json
{
  "id": 6,
  "slug": "minecraft",
  "title": "Minecraft",
  "image": "https://images.igdb.com/...",
  "price": 19.99,
  "originalPrice": 29.99,
  "discount": 33,
  "platform": "Steam",
  "region": "GLOBAL",
  "productType": "Game",
  "hasCashback": true,
  "cashbackPercent": 25,
  "releaseDate": "2011-11-18",
  "developer": "Mojang Studios",
  "publisher": "Microsoft",
  "genres": ["Sandbox", "Survival", "Adventure"],
  "description": "Minecraft is a sandbox video game...",
  "screenshots": [
    "https://images.igdb.com/igdb/image/upload/t_screenshot_big/bhthljrufxn2fvfx9zjz.jpg",
    "https://images.igdb.com/igdb/image/upload/t_screenshot_big/sc5ucy.jpg",
    "https://images.igdb.com/igdb/image/upload/t_screenshot_big/sc5ucz.jpg"
  ]
}
```

## How to Run

```bash
cd /Users/volkanoluc/Projects/eneba/api

# Install dependencies
composer install

# Run migrations
php artisan migrate

# Seed database
php artisan db:seed

# Build search index
php artisan scout:import "App\Models\Game"

# Start server
php artisan serve --port=8000
```

## Test Commands

```bash
# List games
curl "http://127.0.0.1:8000/api/list?page=1&perPage=5"

# Search
curl "http://127.0.0.1:8000/api/list?search=minecraft"

# Filter by platform
curl "http://127.0.0.1:8000/api/list?platforms=Xbox"

# Filter by type
curl "http://127.0.0.1:8000/api/list?types=DLC"

# Filter by genre
curl "http://127.0.0.1:8000/api/list?genres=horror"

# Filter by price range
curl "http://127.0.0.1:8000/api/list?minPrice=10&maxPrice=30"

# Sort by price
curl "http://127.0.0.1:8000/api/list?sort=price-asc"

# Autocomplete
curl "http://127.0.0.1:8000/api/autocomplete?q=mine&limit=5"

# Game details
curl "http://127.0.0.1:8000/api/games/minecraft"
```

## Notes
- All response formats match the API contract in `endpoints.md`
- Field names are camelCase in API responses (snake_case in database)
- TNTSearch provides fuzzy search with database fallback
- CORS configured for frontend at localhost:3000
- 99 games seeded with real IGDB images
