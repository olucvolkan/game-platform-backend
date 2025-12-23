<?php

namespace App\Services;

use App\Models\Game;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class SearchService
{
    /**
     * Search for games using fuzzy matching.
     *
     * @param string $query The search query
     * @param int $limit Maximum results to return
     * @return Collection
     */
    public function search(string $query, int $limit = 10): Collection
    {
        // Use Scout search if index exists
        try {
            $results = Game::search($query)->take($limit)->get();

            if ($results->isNotEmpty()) {
                return $results;
            }
        } catch (\Exception $e) {
            // Fall back to database search if TNTSearch index doesn't exist
        }

        // Fallback to database fuzzy search
        return $this->databaseSearch($query, $limit);
    }

    /**
     * Search games by IDs from scout results.
     *
     * @param array $ids Game IDs
     * @return Collection
     */
    public function getByIds(array $ids): Collection
    {
        if (empty($ids)) {
            return new Collection();
        }

        return Game::with('genres')
            ->whereIn('id', $ids)
            ->orderByRaw('FIELD(id, ' . implode(',', $ids) . ')')
            ->get();
    }

    /**
     * Database-based fuzzy search as fallback.
     *
     * @param string $query The search query
     * @param int $limit Maximum results
     * @return Collection
     */
    protected function databaseSearch(string $query, int $limit = 10): Collection
    {
        $query = strtolower(trim($query));

        // Create variations for common typos
        $searchPatterns = $this->generateSearchPatterns($query);

        return Game::where(function ($q) use ($query, $searchPatterns) {
            // Exact match (highest priority)
            $q->whereRaw('LOWER(title) = ?', [$query]);

            // Title starts with query
            $q->orWhereRaw('LOWER(title) LIKE ?', [$query . '%']);

            // Title contains query
            $q->orWhereRaw('LOWER(title) LIKE ?', ['%' . $query . '%']);

            // Developer/Publisher matches
            $q->orWhereRaw('LOWER(developer) LIKE ?', ['%' . $query . '%']);
            $q->orWhereRaw('LOWER(publisher) LIKE ?', ['%' . $query . '%']);

            // Search patterns for typo tolerance
            foreach ($searchPatterns as $pattern) {
                $q->orWhereRaw('LOWER(title) LIKE ?', ['%' . $pattern . '%']);
            }
        })
            ->orderByRaw("
                CASE
                    WHEN LOWER(title) = ? THEN 1
                    WHEN LOWER(title) LIKE ? THEN 2
                    WHEN LOWER(title) LIKE ? THEN 3
                    ELSE 4
                END
            ", [$query, $query . '%', '%' . $query . '%'])
            ->orderBy('popularity_score', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Generate search patterns for typo tolerance.
     *
     * @param string $query Original query
     * @return array
     */
    protected function generateSearchPatterns(string $query): array
    {
        $patterns = [];
        $query = strtolower($query);

        // Remove duplicate characters (e.g., "ffa" -> "fa")
        $deduplicated = preg_replace('/(.)\1+/', '$1', $query);
        if ($deduplicated !== $query) {
            $patterns[] = $deduplicated;
        }

        // Common character swaps
        $swaps = [
            'i' => 'e',
            'e' => 'i',
            'a' => 'e',
            'c' => 'k',
            'k' => 'c',
            'ph' => 'f',
            'f' => 'ph',
        ];

        foreach ($swaps as $from => $to) {
            $swapped = str_replace($from, $to, $query);
            if ($swapped !== $query) {
                $patterns[] = $swapped;
            }
        }

        // Remove common vowels for consonant-only matching
        $consonantsOnly = preg_replace('/[aeiou]/', '', $query);
        if (strlen($consonantsOnly) >= 2) {
            $patterns[] = $consonantsOnly;
        }

        return array_unique($patterns);
    }

    /**
     * Get autocomplete suggestions.
     *
     * @param string $query Search query
     * @param int $limit Maximum suggestions
     * @return Collection
     */
    public function autocomplete(string $query, int $limit = 10): Collection
    {
        return $this->search($query, $limit);
    }
}
