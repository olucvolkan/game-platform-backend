# Task: Authentication & User Favorites Implementation

## Status
- Created: 2024-12-23 16:20
- Started: 2024-12-23 16:25
- Completed: 2024-12-23 16:45

## Description
Implement user authentication (login/register) and user-linked favorites system.
- Users can register and login with username/password
- Logged-in users can favorite games (persisted to database)
- Users can view their favorites list

## Optional Requirements Implemented
- [x] Login system (simple username/password)
- [x] Favorites linked to user account

## Assigned Agents
- [x] @backend-developer - Laravel auth with Sanctum, user favorites API
- [x] @database-planner - User and favorites tables
- [x] @vue-nuxt-expert - Login/register pages, auth state, favorites UI
- [x] @ui-designer - Login modal/page design

## Implementation Summary

### Backend (Laravel)

**Files Created:**
- `app/Http/Controllers/Api/AuthController.php` - Register, login, logout, user endpoints
- `app/Http/Controllers/Api/FavoritesController.php` - CRUD for user favorites
- `database/migrations/2025_12_23_153542_create_user_favorites_table.php` - Pivot table

**Files Modified:**
- `app/Models/User.php` - Added HasApiTokens trait and favorites() relationship
- `routes/api.php` - Added auth and favorites routes

**API Endpoints:**
| Method | Endpoint | Auth | Description |
|--------|----------|------|-------------|
| POST | /api/register | No | Register new user |
| POST | /api/login | No | Login, returns token |
| POST | /api/logout | Yes | Revoke token |
| GET | /api/user | Yes | Get current user |
| GET | /api/favorites | Yes | List user's favorites |
| POST | /api/favorites/{gameId} | Yes | Add to favorites |
| DELETE | /api/favorites/{gameId} | Yes | Remove from favorites |

### Frontend (Nuxt.js)

**Files Created:**
- `composables/useAuth.ts` - Auth state, login/register/logout methods
- `components/auth/AuthModal.vue` - Tabbed login/register modal

**Files Modified:**
- `types/index.ts` - Added User, AuthResponse, LoginCredentials, RegisterCredentials, FavoriteGame types
- `composables/useFavorites.ts` - API sync when authenticated, localStorage fallback
- `components/header/UserActions.vue` - Auth state display, user menu, logout
- `pages/favorites.vue` - Auth-aware empty states
- `components/product/ProductCard.vue` - Login prompt on favorite for guests
- `composables/useLanguage.ts` - Added 17 translation keys for auth UI

## Features

### Authentication
- JWT token-based auth via Laravel Sanctum
- Token stored in localStorage + cookie for SSR
- Auto-initialization on page load
- Login/Register modal with form validation
- User dropdown menu with avatar

### Favorites
- Authenticated: Sync with API, persisted to database
- Guest: localStorage fallback
- Optimistic updates with error rollback
- Login prompt for guests trying to favorite
- Clear all favorites with confirmation

## Acceptance Criteria
- [x] User can register with email/password
- [x] User can login and receive auth token
- [x] Auth token stored in localStorage/cookie
- [x] User can logout
- [x] Logged-in user can add/remove favorites
- [x] Favorites persisted to database
- [x] Favorites page shows user's saved games
- [x] Guest users prompted to login when adding favorites

## Verification Commands

```bash
# Start Laravel API
cd api && php artisan serve

# Start Nuxt frontend
cd frontend && npm run dev

# Test register
curl -X POST http://localhost:8000/api/register \
  -H "Content-Type: application/json" \
  -d '{"name":"Test","email":"test@example.com","password":"password123","password_confirmation":"password123"}'

# Test login
curl -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{"email":"test@example.com","password":"password123"}'
```
