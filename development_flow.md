# Development Flow

This document explains the development lifecycle, agent workflow, and custom commands for the Eneba clone project.

## Project Architecture

```
eneba/
├── api/                    # Laravel 12 API Backend (PHP 8.4)
│   ├── app/
│   │   ├── Http/Controllers/Api/   # Request/Response handling
│   │   ├── Services/               # Business logic layer
│   │   ├── Models/                 # Eloquent models & relations
│   │   └── Http/Resources/         # API response transformers
│   ├── database/
│   │   ├── migrations/
│   │   ├── factories/              # Test data factories
│   │   └── seeders/
│   ├── tests/Feature/              # E2E API tests
│   └── .agent/                     # API-specific task tracking
│
├── frontend/               # Nuxt.js Frontend (TypeScript)
│   ├── components/                 # Vue components
│   ├── composables/                # Reusable logic hooks
│   ├── pages/                      # File-based routing
│   ├── layouts/
│   └── .agent/                     # Frontend-specific task tracking
│
└── .agent/                 # Root task tracking & SOPs
    ├── task/
    │   ├── todo/           # New tasks waiting to start
    │   ├── inprogress/     # Currently active work
    │   └── done/           # Completed tasks
    └── sop/                # Standard Operating Procedures
```

---

## Development Lifecycle: BFF (Backend For Frontend)

The project follows a **Frontend-First Development** approach where UI needs drive API design.

### Phase 1: Frontend Design
```
Figma Design → Vue Components → Define Data Requirements
```
- Create Vue components based on designs
- Identify props, state, and data fields needed
- Document expected API response structure

### Phase 2: API Contract
```
Frontend Needs → API Endpoints → Request/Response JSON Schema
```
- Define endpoint URLs and HTTP methods
- Specify request parameters and validation
- Document exact JSON response format (camelCase)

### Phase 3: Backend Implementation
```
Controller → Service → Repository/Model
```
- **Controller**: Handle HTTP requests, validation, return responses
- **Service**: Business logic, data transformation
- **Model**: Database queries, relationships, scopes

### Phase 4: Integration & Testing
```
Connect Frontend → E2E Tests → Deploy
```
- Frontend calls real API endpoints
- Write feature tests with `DatabaseTransactions`
- Deploy via GitHub Actions CI/CD

---

## .agent Directory Structure

The `.agent/` directory is the central hub for task tracking and knowledge management.

### Task Lifecycle

```
.agent/task/todo/          # 1. Create new task file here
       ↓
.agent/task/inprogress/    # 2. Move file when starting work
       ↓
.agent/task/done/          # 3. Move file when completed
```

### Task File Format

```markdown
# Task: [Task Name]

## Status
- Created: YYYY-MM-DD HH:MM
- Started: YYYY-MM-DD HH:MM
- Completed: YYYY-MM-DD HH:MM

## Description
[What needs to be done]

## Files Created/Modified
- `/path/to/file.php` - Description

## API Response Examples
[JSON examples if applicable]

## How to Test
[Commands to verify the implementation]

## Notes
[Any important considerations]
```

### SOP (Standard Operating Procedures)

Location: `.agent/sop/`

SOPs document solutions to complex problems for future reference:

```markdown
# SOP: [Problem Name]

## Problem
[What was happening]

## Root Cause
[Why it was happening]

## Solution
[How it was fixed with code examples]

## Files Modified
[List of changed files]

## Verification
[How to verify the fix works]
```

**Example SOPs:**
- `igdb-api-query-format.md` - IGDB Apicalypse query syntax rules

---

## Custom Commands (Agents)

### `/bff-task`
**Full BFF Workflow** - Complete feature implementation from design to deployment.

```
1. Create task file in .agent/task/todo/
2. Frontend Phase: Components → Props → Data fields
3. API Contract Phase: Endpoints → Request/Response JSON
4. Backend Phase: Controller → Service → Model
5. Documentation Phase: Update task file → Move to done/
```

**When to use:** New features, full-stack implementations

---

### `/fix`
**Bug Fix Mode** - Minimal changes to fix specific issues.

```
1. Identify the bug
2. Make minimal necessary changes
3. Document fix in .agent/sop/ if complex
4. No new features or refactoring
```

**When to use:** Bug reports, error fixes, quick patches

---

### `/tasks`
**Task Executor** - Execute a specific task from `.agent/task/` end-to-end.

```
1. Read task from .agent/task/todo/ or inprogress/
2. Execute all steps in the task file
3. Move to .agent/task/done/ when complete
```

**When to use:** Continue existing task, follow documented plan

---

### `/update-doc`
**Documentation Update** - Update documentation after changes.

```
1. Analyze recent changes
2. Update CLAUDE.md if architecture changed
3. Update README files if setup changed
4. Create/update SOPs for complex solutions
```

**When to use:** After completing features, after bug fixes

---

## Backend Patterns

### Model-Controller-Service Architecture

```
Request → Controller → Service → Model → Database
                ↓
           Resource → JSON Response
```

**Controller** (`app/Http/Controllers/Api/`)
```php
public function index(GameListRequest $request): JsonResponse
{
    $games = $this->gameService->getFilteredGames($request->validated());
    return response()->json(new PaginatedGameCollection($games));
}
```

**Service** (`app/Services/`)
```php
public function getFilteredGames(array $filters): LengthAwarePaginator
{
    return Game::query()
        ->when($filters['platform'], fn($q) => $q->byPlatform($filters['platform']))
        ->paginate($filters['perPage']);
}
```

**Model** (`app/Models/`)
```php
public function scopeByPlatform($query, string $platform): Builder
{
    return $query->where('platform', $platform);
}
```

### Testing with DatabaseTransactions

```php
class GameListTest extends TestCase
{
    use DatabaseTransactions;  // NOT RefreshDatabase

    public function test_can_filter_by_platform(): void
    {
        Game::factory()->steam()->count(3)->create();

        $response = $this->getJson('/api/list?platforms=Steam');

        $response->assertStatus(200);
    }
}
```

---

## Frontend Patterns

### Nuxt.js Composables

```typescript
// composables/useGames.ts
export function useGames() {
  const games = ref<Game[]>([])

  const fetchGames = async (filters: GameFilters) => {
    const { data } = await useFetch<GameResponse>('/api/list', {
      query: filters
    })
    games.value = data.value?.data ?? []
  }

  return { games, fetchGames }
}
```

### Component Structure

```vue
<script setup lang="ts">
// Props
interface Props {
  game: Game
}
const props = defineProps<Props>()

// Composables
const { addToFavorites } = useFavorites()
</script>

<template>
  <div class="bg-primary-900 rounded-lg">
    <!-- Component content -->
  </div>
</template>
```

---

## CI/CD Pipeline

### GitHub Actions Workflow

```yaml
# .github/workflows/tests.yml
name: Tests

on:
  push:
    branches: [main, master, develop]
  pull_request:
    branches: [main, master, develop]

jobs:
  tests:
    runs-on: ubuntu-latest
    services:
      postgres:
        image: postgres:15
        env:
          POSTGRES_DB: eneba_test
    steps:
      - uses: actions/checkout@v4
      - uses: shivammathur/setup-php@v2
        with:
          php-version: '8.4'
      - run: composer install
      - run: php artisan migrate --force
      - run: php artisan test
```

---

## Key Conventions

1. **API Response Format**: Always camelCase in JSON responses
2. **Database Columns**: Always snake_case in database
3. **Frontend-First**: Define UI needs before building API
4. **Task Tracking**: Always document work in `.agent/task/`
5. **SOP Creation**: Document complex bug fixes for future reference
6. **Minimal Changes**: `/fix` command = no new features
7. **Test with Transactions**: Use `DatabaseTransactions` trait

---

## Quick Reference

| Action | Command |
|--------|---------|
| New feature | `/bff-task` |
| Bug fix | `/fix` |
| Continue task | `/tasks` |
| Update docs | `/update-doc` |

| Directory | Purpose |
|-----------|---------|
| `.agent/task/todo/` | New tasks |
| `.agent/task/inprogress/` | Active work |
| `.agent/task/done/` | Completed |
| `.agent/sop/` | Problem solutions |
