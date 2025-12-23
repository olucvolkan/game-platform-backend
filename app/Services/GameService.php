<?php

namespace App\Services;

use App\Models\Game;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class GameService
{
    public function __construct(
        protected SearchService $searchService
    ) {}

    /**
     * Get paginated list of games with filters.
     *
     * @param array $filters
     * @return LengthAwarePaginator
     */
    public function getFilteredGames(array $filters): LengthAwarePaginator
    {
        $query = Game::with('genres');

        // Apply search if provided
        if (!empty($filters['search'])) {
            $searchResults = $this->searchService->search($filters['search'], 1000);
            $ids = $searchResults->pluck('id')->toArray();

            if (empty($ids)) {
                // Return empty paginator if no search results
                return Game::where('id', 0)->paginate($filters['perPage'] ?? 20);
            }

            $query->whereIn('id', $ids);
        }

        // Apply filters
        $query = $this->applyFilters($query, $filters);

        // Apply sorting
        $query = $this->applySorting($query, $filters['sort'] ?? 'popularity', !empty($filters['search']));

        // Paginate
        return $query->paginate(
            perPage: $filters['perPage'] ?? 20,
            page: $filters['page'] ?? 1
        );
    }

    /**
     * Apply filters to the query.
     *
     * @param Builder $query
     * @param array $filters
     * @return Builder
     */
    protected function applyFilters(Builder $query, array $filters): Builder
    {
        // Platform filter
        if (!empty($filters['platforms'])) {
            $query->byPlatform($filters['platforms']);
        }

        // Product type filter
        if (!empty($filters['types'])) {
            $query->byProductType($filters['types']);
        }

        // Genre filter
        if (!empty($filters['genres'])) {
            $query->byGenres($filters['genres']);
        }

        // Price range filter
        $minPrice = isset($filters['minPrice']) ? (float) $filters['minPrice'] : null;
        $maxPrice = isset($filters['maxPrice']) ? (float) $filters['maxPrice'] : null;

        if ($minPrice !== null || $maxPrice !== null) {
            $query->byPriceRange($minPrice, $maxPrice);
        }

        return $query;
    }

    /**
     * Apply sorting to the query.
     *
     * @param Builder $query
     * @param string $sort
     * @param bool $isSearchQuery
     * @return Builder
     */
    protected function applySorting(Builder $query, string $sort, bool $isSearchQuery = false): Builder
    {
        // If it's a search query and sort is default, maintain search relevance order
        if ($isSearchQuery && $sort === 'popularity') {
            return $query;
        }

        return $query->sortBy($sort);
    }

    /**
     * Get a single game by slug with all details.
     *
     * @param string $slug
     * @return Game|null
     */
    public function getBySlug(string $slug): ?Game
    {
        return Game::with(['genres', 'screenshots'])
            ->where('slug', $slug)
            ->first();
    }

    /**
     * Get games by IDs.
     *
     * @param array $ids
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getByIds(array $ids): \Illuminate\Database\Eloquent\Collection
    {
        return Game::with('genres')
            ->whereIn('id', $ids)
            ->get();
    }
}
