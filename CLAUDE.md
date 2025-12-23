# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

Eneba.com clone - a game marketplace with 100+ games from IGDB. Backend in PHP (Laravel 12), frontend in TypeScript (Vue/Nuxt).

## Architecture

```
api/          → Laravel 12 API-only (PHP)
frontend/     → Vue.js / Nuxt.js (TypeScript)
.agent/       → Task tracking & SOPs
```

### Backend (api/)
- **Auth**: JWT via Laravel Sanctum
- **Database**: PostgreSQL
- **Pattern**: Model-Controller-Service
  - Controllers: request/response handling
  - Services: business logic
  - Models: data relations and scopes
- **Testing**: E2E with `DatabaseTransactions` trait (NOT `RefreshDatabase`)

### Frontend (frontend/)
- **Framework**: Nuxt.js with auto-imports
- **Styling**: Tailwind CSS
- **State**: Pinia or native `useState`/`useAsyncData`
- **Colors**: Primary `#4618ac`, Accent `#fad318`, Filter BG `#5825cc`
- **Font**: Metropolis

## Agent Workflow

All development tracked via `.agent/` directory:

- `.agent/task/todo/` → New tasks
- `.agent/task/inprogress/` → Active work (move file here when starting)
- `.agent/task/done/` → Completed tasks
- `.agent/sop/` → Bug fixes and complex logic patterns (reference for learning)

## Custom Commands

| Command | Purpose |
|---------|---------|
| `/bff-tasks` | Full BFF workflow: Frontend → API Contract → Backend → Docs |
| `/fix` | Bug fixes only. Minimal changes, no new features |
| `/tasks` | Execute task from `.agent/task/` end-to-end |
| `/update-doc` | Update documentation after changes |

### /bff-tasks Flow
1. Create task file in `.agent/task/`
2. Frontend phase: Figma designs → components → props/data fields
3. API Contract phase: Define endpoints, request/response JSON
4. Backend phase: Implement with Controller → Service → Repository
5. Documentation phase: Summary and follow-ups

## Core Requirements

### API Endpoints
- `/api/list` - Paginated game list (JSON)
- `/list` - User-facing paginated list
- `/list?search=<query>` - Fuzzy search (e.g., "ffa" matches "fifa")
- Autocomplete for search bar

### Optional Features
- Game details page
- Login/auth
- Cart with hover popup
- Favorites/wishlist
- Language switch (3 languages)
- Currency conversion (3 currencies)
- Synonym search (e.g., "GTA 5" = "GTA V")

## Key Conventions

- API responses must match frontend expectations exactly (no renamed/extra fields)
- Frontend-first development: define UI needs, then derive API contracts
- Errors logged to `.agent/sop/` with context and recommended fixes
- Reference `.agent/sop/` before implementing similar fixes
