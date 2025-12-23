# Eneba Clone - API

A Laravel 12 API backend for the Eneba game marketplace clone.

## Production

**Live API:** https://game-platform-backend-master-mlenvd.laravel.cloud/

**Frontend:** https://game-platform-production-9b92.up.railway.app/list

## Tech Stack

- **Framework:** Laravel 12 (PHP 8.4)
- **Database:** PostgreSQL
- **Auth:** Laravel Sanctum (JWT)
- **Search:** TNTSearch for fuzzy matching
- **Architecture:** Model-Controller-Service

## API Endpoints

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/list` | Paginated game list with filters |
| GET | `/api/autocomplete?q=` | Search suggestions |
| GET | `/api/games/{slug}` | Game details |
| POST | `/api/register` | User registration |
| POST | `/api/login` | User login |
| POST | `/api/logout` | User logout |
| GET | `/api/user` | Current user info |
| GET | `/api/favorites` | User favorites |
| POST | `/api/favorites/{id}` | Add to favorites |
| DELETE | `/api/favorites/{id}` | Remove from favorites |

## Local Development

```bash
# Install dependencies
composer install

# Copy environment file
cp .env.example .env

# Generate app key
php artisan key:generate

# Run migrations
php artisan migrate

# Seed database (100+ games from IGDB)
php artisan db:seed

# Start server
php artisan serve --port=8000
```

## Testing

```bash
# Run all tests
php artisan test

# Run specific test file
php artisan test --filter=AuthTest
```

## Environment Variables

```env
# Database
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=eneba
DB_USERNAME=postgres
DB_PASSWORD=

# IGDB API (for importing games)
IGDB_CLIENT_ID=
IGDB_CLIENT_SECRET=

# Frontend URL (CORS)
FRONTEND_URL=http://localhost:3000
```

## Project Structure

```
app/
├── Http/
│   ├── Controllers/Api/    # API controllers
│   ├── Requests/           # Form validation
│   └── Resources/          # JSON transformers
├── Models/                 # Eloquent models
└── Services/               # Business logic

database/
├── factories/              # Test factories
├── migrations/             # Database schema
└── seeders/                # Sample data

tests/Feature/              # E2E API tests
```

## License

MIT
