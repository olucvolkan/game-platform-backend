# Frontend Integration Guide

This document explains how to connect the Nuxt.js frontend to the Laravel API backend.

---

## Quick Start

### 1. Start the Laravel API

```bash
cd api
composer install
php artisan migrate
php artisan igdb:import --count=100  # Import 100+ games from IGDB
php artisan serve  # Starts at http://localhost:8000
```

### 2. Start the Nuxt Frontend

```bash
cd frontend
npm install
npm run dev  # Starts at http://localhost:3000
```

### 3. Configure API URL (Optional)

The frontend is pre-configured to connect to `http://localhost:8000/api`. To change this:

```bash
# In frontend/.env
NUXT_PUBLIC_API_BASE=http://your-api-domain.com/api
```

---

## API Base URL

| Environment | URL |
|-------------|-----|
| Development | `http://localhost:8000/api` |
| Production | Set via `NUXT_PUBLIC_API_BASE` environment variable |

---

## Available Endpoints

### 1. Game List - `GET /api/list`

Returns a paginated list of games with optional filtering and search.

#### Query Parameters

| Parameter | Type | Default | Description |
|-----------|------|---------|-------------|
| `page` | integer | 1 | Current page number |
| `perPage` | integer | 20 | Items per page (max: 100) |
| `search` | string | - | Search query (supports fuzzy matching) |
| `sort` | string | popularity | Sort option: `popularity`, `price-asc`, `price-desc`, `newest`, `discount` |
| `minPrice` | number | - | Minimum price filter |
| `maxPrice` | number | - | Maximum price filter |
| `types` | string | - | Comma-separated product types: `Game,DLC` |
| `platforms` | string | - | Comma-separated platforms: `Steam,Xbox,PlayStation,Nintendo,Epic,GOG` |
| `genres` | string | - | Comma-separated genres: `action,rpg,shooter,strategy` |

#### Example Request

```bash
# Fetch first page
curl "http://localhost:8000/api/list"

# Search with filters
curl "http://localhost:8000/api/list?search=minecraft&sort=price-asc&platforms=Steam,Xbox&page=1&perPage=20"
```

#### Response Format

```json
{
  "data": [
    {
      "id": 1,
      "slug": "minecraft",
      "title": "Minecraft",
      "image": "https://images.igdb.com/igdb/image/upload/t_cover_big/co1wyy.jpg",
      "price": 19.99,
      "originalPrice": 29.99,
      "discount": 33,
      "platform": "Steam",
      "region": "GLOBAL",
      "productType": "Game",
      "hasCashback": true,
      "cashbackPercent": 25,
      "releaseDate": "2011-11-18",
      "developer": "Mojang",
      "publisher": "Microsoft",
      "genres": ["Sandbox", "Survival"]
    }
  ],
  "meta": {
    "total": 110,
    "page": 1,
    "perPage": 20,
    "lastPage": 6
  }
}
```

---

### 2. Autocomplete - `GET /api/autocomplete`

Returns search suggestions for the autocomplete feature. Supports fuzzy matching.

#### Query Parameters

| Parameter | Type | Default | Description |
|-----------|------|---------|-------------|
| `q` | string | required | Search query (minimum 2 characters) |
| `limit` | integer | 10 | Maximum suggestions (max: 20) |

#### Example Request

```bash
curl "http://localhost:8000/api/autocomplete?q=mine&limit=5"
```

#### Response Format

```json
{
  "suggestions": [
    {
      "id": 1,
      "title": "Minecraft",
      "slug": "minecraft",
      "image": "https://images.igdb.com/igdb/image/upload/t_cover_big/co1wyy.jpg"
    },
    {
      "id": 15,
      "title": "Minecraft Dungeons",
      "slug": "minecraft-dungeons",
      "image": "https://images.igdb.com/igdb/image/upload/t_cover_big/co2abc.jpg"
    }
  ]
}
```

#### Fuzzy Search Examples

The search supports typo tolerance:
- `ffa` -> matches "FIFA 24"
- `mincraft` -> matches "Minecraft"
- `zel` -> matches "The Legend of Zelda"

---

### 3. Game Details - `GET /api/games/{slug}`

Returns detailed information about a single game.

#### Path Parameters

| Parameter | Type | Description |
|-----------|------|-------------|
| `slug` | string | Game URL slug |

#### Example Request

```bash
curl "http://localhost:8000/api/games/minecraft"
```

#### Response Format

```json
{
  "id": 1,
  "slug": "minecraft",
  "title": "Minecraft",
  "image": "https://images.igdb.com/igdb/image/upload/t_cover_big/co1wyy.jpg",
  "price": 19.99,
  "originalPrice": 29.99,
  "discount": 33,
  "platform": "Steam",
  "region": "GLOBAL",
  "productType": "Game",
  "hasCashback": true,
  "cashbackPercent": 25,
  "releaseDate": "2011-11-18",
  "developer": "Mojang",
  "publisher": "Microsoft",
  "genres": ["Sandbox", "Survival"],
  "description": "Minecraft is a sandbox video game...",
  "screenshots": [
    "https://images.igdb.com/igdb/image/upload/t_screenshot_big/sc1.jpg",
    "https://images.igdb.com/igdb/image/upload/t_screenshot_big/sc2.jpg"
  ]
}
```

#### Error Response (404)

```json
{
  "error": "Game not found",
  "statusCode": 404
}
```

---

## Using the API in Nuxt.js

### Option 1: Use the `useApi` Composable (Recommended)

The frontend includes a pre-built composable at `composables/useApi.ts`:

```typescript
// In your component or page
const { fetchGames, fetchAutocomplete, fetchGameBySlug } = useApi()

// Fetch paginated games
const { data } = await fetchGames({
  page: 1,
  search: 'minecraft',
  sort: 'price-asc',
  platforms: ['Steam', 'Xbox']
})

// Get autocomplete suggestions
const suggestions = await fetchAutocomplete('mine', 5)

// Fetch single game
const game = await fetchGameBySlug('minecraft')
```

### Option 2: Use `useFetch` Directly

```typescript
// In your component or page
const config = useRuntimeConfig()

// Fetch games list
const { data: games } = await useFetch(`${config.public.apiBase}/list`, {
  query: {
    page: 1,
    perPage: 20,
    search: 'minecraft'
  }
})

// Fetch autocomplete
const { data: autocomplete } = await useFetch(`${config.public.apiBase}/autocomplete`, {
  query: { q: 'mine', limit: 5 }
})
```

### Option 3: Use `$fetch` for Non-SSR Calls

```typescript
// Client-side only
const games = await $fetch('/api/list', {
  query: { page: 1, search: 'minecraft' }
})
```

---

## TypeScript Types

The frontend includes all necessary types in `types/index.ts`:

```typescript
// Game object
interface Game {
  id: number
  slug: string
  title: string
  image: string
  price: number
  originalPrice: number
  discount: number
  platform: Platform
  region: Region
  productType: ProductType
  hasCashback: boolean
  cashbackPercent: number
  releaseDate?: string
  developer?: string
  publisher?: string
  genres?: string[]
  description?: string
  screenshots?: string[]
}

// Filter parameters for API calls
interface FilterParams {
  page?: number
  perPage?: number
  search?: string
  sort?: SortOption
  minPrice?: number
  maxPrice?: number
  types?: ProductType[]
  platforms?: Platform[]
  genres?: string[]
}

// Paginated response wrapper
interface PaginatedResponse<T> {
  data: T[]
  meta: {
    total: number
    page: number
    perPage: number
    lastPage: number
  }
}

// Autocomplete suggestion
interface AutocompleteSuggestion {
  id: number
  title: string
  slug: string
  image?: string
}
```

---

## CORS Configuration

The Laravel API is configured to accept requests from the frontend. In development:

- Frontend: `http://localhost:3000`
- API: `http://localhost:8000`

For production, update `config/cors.php` in Laravel:

```php
'allowed_origins' => [
    'https://your-frontend-domain.com',
],
```

---

## Error Handling

All endpoints may return these error responses:

### 400 Bad Request

```json
{
  "error": "Invalid request parameters",
  "statusCode": 400,
  "details": {
    "field": "error description"
  }
}
```

### 404 Not Found

```json
{
  "error": "Resource not found",
  "statusCode": 404
}
```

### 500 Internal Server Error

```json
{
  "error": "Internal server error",
  "statusCode": 500
}
```

---

## Development Tips

### 1. Test API Connection

```bash
# Check if API is running
curl http://localhost:8000/api/list

# Test search
curl "http://localhost:8000/api/list?search=zelda"

# Test autocomplete
curl "http://localhost:8000/api/autocomplete?q=mine"
```

### 2. Import More Games

```bash
cd api
php artisan igdb:import --count=200  # Import 200 games
```

### 3. Check Database

```bash
cd api
php artisan tinker
>>> App\Models\Game::count()
# Should return 100+
```

### 4. Clear Cache

```bash
cd api
php artisan cache:clear
php artisan config:clear
```

---

## Deployment Checklist

1. [ ] Set `NUXT_PUBLIC_API_BASE` environment variable to production API URL
2. [ ] Configure CORS in Laravel for production domain
3. [ ] Run `php artisan igdb:import` to populate games
4. [ ] Test all endpoints in production environment
5. [ ] Enable HTTPS for both frontend and API

---

## File Structure Reference

```
frontend/
  composables/
    useApi.ts         # API client composable
    useSearch.ts      # Fuzzy search with Fuse.js
    useCart.ts        # Shopping cart logic
    useFavorites.ts   # Favorites/wishlist
    useLanguage.ts    # i18n with 3 languages
    useFilters.ts     # Filter state management
  types/
    index.ts          # TypeScript type definitions
  pages/
    list.vue          # Game list page (/list)
    game/[slug].vue   # Game details page
    favorites.vue     # Favorites page

api/
  app/Http/Controllers/Api/
    GameListController.php      # GET /api/list
    AutocompleteController.php  # GET /api/autocomplete
    GameController.php          # GET /api/games/{slug}
  app/Services/
    GameService.php             # Game business logic
    SearchService.php           # Fuzzy search implementation
    IgdbService.php             # IGDB API integration
```

---

## Support

For issues or questions:
1. Check Laravel logs: `storage/logs/laravel.log`
2. Check browser console for frontend errors
3. Verify API is running: `curl http://localhost:8000/api/list`
