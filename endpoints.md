# Eneba API Endpoints Documentation

This document defines the API contract between the frontend and backend. The Laravel backend must implement these endpoints exactly as specified.

---

## Base URL

```
Production: https://api.eneba-clone.com
Development: http://localhost:8000
```

---

## 1. Game List Endpoint

### `GET /api/list`

Returns a paginated list of games with optional filtering and search.

#### Query Parameters

| Parameter | Type | Default | Description |
|-----------|------|---------|-------------|
| `page` | integer | 1 | Current page number |
| `perPage` | integer | 20 | Items per page (max: 100) |
| `search` | string | - | Search query (fuzzy matching) |
| `sort` | string | popularity | Sort option: `popularity`, `price-asc`, `price-desc`, `newest`, `discount` |
| `minPrice` | number | - | Minimum price filter |
| `maxPrice` | number | - | Maximum price filter |
| `types` | string | - | Comma-separated product types: `Game,DLC,Subscription,eGift Card` |
| `platforms` | string | - | Comma-separated platforms: `Steam,Xbox,PlayStation,Nintendo,Epic,Origin,GOG,Uplay` |
| `genres` | string | - | Comma-separated genres: `action,rpg,shooter,strategy,simulation,sports,racing,puzzle,horror,sandbox,survival,adventure` |

#### Example Request

```bash
GET /api/list?page=1&perPage=20&search=minecraft&sort=price-asc&types=Game,DLC&platforms=Steam,Xbox
```

#### Response Format

```json
{
  "data": [
    {
      "id": 1,
      "slug": "minecraft",
      "title": "Minecraft",
      "image": "https://example.com/images/minecraft.jpg",
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
    },
    {
      "id": 2,
      "slug": "minecraft-dlc",
      "title": "Minecraft - Season Pass",
      "image": "https://example.com/images/minecraft-dlc.jpg",
      "price": 14.99,
      "originalPrice": 19.99,
      "discount": 25,
      "platform": "Steam",
      "region": "GLOBAL",
      "productType": "DLC",
      "hasCashback": false,
      "cashbackPercent": 0,
      "developer": "Mojang",
      "publisher": "Microsoft",
      "genres": ["Sandbox", "Survival"]
    }
  ],
  "meta": {
    "total": 470,
    "page": 1,
    "perPage": 20,
    "lastPage": 24
  }
}
```

#### Response Fields

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `data` | array | Yes | Array of game objects |
| `data[].id` | integer | Yes | Unique game ID |
| `data[].slug` | string | Yes | URL-friendly identifier |
| `data[].title` | string | Yes | Game title |
| `data[].image` | string | Yes | Cover image URL |
| `data[].price` | number | Yes | Current price (after discount) |
| `data[].originalPrice` | number | Yes | Original price before discount |
| `data[].discount` | integer | Yes | Discount percentage (0-100) |
| `data[].platform` | string | Yes | Platform name |
| `data[].region` | string | Yes | Region code |
| `data[].productType` | string | Yes | Product type |
| `data[].hasCashback` | boolean | Yes | Has cashback offer |
| `data[].cashbackPercent` | integer | Yes | Cashback percentage |
| `data[].releaseDate` | string | No | Release date (YYYY-MM-DD) |
| `data[].developer` | string | No | Developer name |
| `data[].publisher` | string | No | Publisher name |
| `data[].genres` | array | No | Array of genre strings |
| `meta.total` | integer | Yes | Total items matching filter |
| `meta.page` | integer | Yes | Current page |
| `meta.perPage` | integer | Yes | Items per page |
| `meta.lastPage` | integer | Yes | Total pages |

---

## 2. Search Autocomplete Endpoint

### `GET /api/autocomplete`

Returns search suggestions for autocomplete functionality. Uses fuzzy matching.

#### Query Parameters

| Parameter | Type | Default | Description |
|-----------|------|---------|-------------|
| `q` | string | - | Search query (min 2 characters) |
| `limit` | integer | 10 | Maximum suggestions (max: 20) |

#### Example Request

```bash
GET /api/autocomplete?q=mine&limit=5
```

#### Response Format

```json
{
  "suggestions": [
    {
      "id": 1,
      "title": "Minecraft",
      "slug": "minecraft",
      "image": "https://example.com/images/minecraft.jpg"
    },
    {
      "id": 15,
      "title": "Minecraft Dungeons",
      "slug": "minecraft-dungeons",
      "image": "https://example.com/images/minecraft-dungeons.jpg"
    },
    {
      "id": 23,
      "title": "Minecraft Legends",
      "slug": "minecraft-legends",
      "image": "https://example.com/images/minecraft-legends.jpg"
    }
  ]
}
```

#### Response Fields

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `suggestions` | array | Yes | Array of suggestion objects |
| `suggestions[].id` | integer | Yes | Game ID |
| `suggestions[].title` | string | Yes | Game title |
| `suggestions[].slug` | string | Yes | URL-friendly identifier |
| `suggestions[].image` | string | No | Thumbnail image URL |

#### Fuzzy Search Behavior

The search should match:
- Exact matches: "minecraft" -> "Minecraft"
- Partial matches: "mine" -> "Minecraft"
- Typo tolerance: "mincraft" -> "Minecraft"
- Similar matches: "ffa", "fffa" -> "FIFA 24"

---

## 3. Game Details Endpoint

### `GET /api/games/{slug}`

Returns detailed information about a single game.

#### Path Parameters

| Parameter | Type | Description |
|-----------|------|-------------|
| `slug` | string | Game slug identifier |

#### Example Request

```bash
GET /api/games/minecraft
```

#### Response Format

```json
{
  "id": 1,
  "slug": "minecraft",
  "title": "Minecraft",
  "image": "https://example.com/images/minecraft.jpg",
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
  "description": "Minecraft is a sandbox video game developed by Mojang Studios. Players explore a blocky, procedurally generated, three-dimensional world with virtually infinite terrain, and may discover and extract raw materials, craft tools and items, and build structures or earthworks.",
  "screenshots": [
    "https://example.com/images/minecraft-screenshot-1.jpg",
    "https://example.com/images/minecraft-screenshot-2.jpg",
    "https://example.com/images/minecraft-screenshot-3.jpg",
    "https://example.com/images/minecraft-screenshot-4.jpg"
  ]
}
```

#### Response Fields

All fields from the list endpoint, plus:

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `description` | string | Yes | Full game description |
| `screenshots` | array | Yes | Array of screenshot URLs |

#### Error Response (404)

```json
{
  "error": "Game not found",
  "statusCode": 404
}
```

---

## 4. User-Facing Endpoints

These endpoints render HTML pages using the same data.

### `GET /list`

User-facing paginated game list page.

- Accepts same query parameters as `/api/list`
- Renders HTML page with game grid
- SEO-optimized with proper meta tags

### `GET /list?search={query}`

User-facing search results page.

- Displays filtered results based on search query
- Shows active filters and result count
- Supports fuzzy search

---

## Type Definitions

### Platform

```typescript
type Platform = 'PC' | 'Steam' | 'Xbox' | 'PlayStation' | 'Nintendo' | 'Origin' | 'Uplay' | 'Epic' | 'GOG'
```

### Region

```typescript
type Region = 'GLOBAL' | 'EU' | 'US' | 'TR' | 'UK' | 'DE' | 'RU' | 'LATAM' | 'ASIA'
```

### ProductType

```typescript
type ProductType = 'Game' | 'DLC' | 'Game Points' | 'Subscription' | 'Software' | 'eGift Card'
```

### SortOption

```typescript
type SortOption = 'popularity' | 'price-asc' | 'price-desc' | 'newest' | 'discount'
```

---

## Error Responses

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

## Implementation Notes

### Fuzzy Search

Use a library like [TNTSearch](https://github.com/teamtnt/laravel-scout-tntsearch-driver) or [Meilisearch](https://www.meilisearch.com/) for fuzzy search implementation.

Search should:
1. Match on `title` (primary, weight 0.7)
2. Match on `developer` (secondary, weight 0.15)
3. Match on `publisher` (tertiary, weight 0.1)
4. Tolerance threshold: 0.4 (allows typos)

### Pagination

- Default page size: 20
- Maximum page size: 100
- Return empty `data` array if page exceeds `lastPage`

### Sorting

When `sort=popularity` (default):
- Games should be ordered by internal popularity score
- If no score exists, order by recent sales or views

### Price Filtering

- Prices should be in EUR (base currency)
- Frontend handles currency conversion display
- `minPrice` and `maxPrice` are inclusive

---

## CORS Configuration

For development, allow:

```
Access-Control-Allow-Origin: http://localhost:3000
Access-Control-Allow-Methods: GET, POST, OPTIONS
Access-Control-Allow-Headers: Content-Type, Authorization
```
