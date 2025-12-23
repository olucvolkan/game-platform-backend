<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\AutocompleteRequest;
use App\Http\Resources\AutocompleteSuggestionResource;
use App\Services\SearchService;
use Illuminate\Http\JsonResponse;

class AutocompleteController extends Controller
{
    public function __construct(
        protected SearchService $searchService
    ) {}

    /**
     * Get autocomplete suggestions for search.
     *
     * @param AutocompleteRequest $request
     * @return JsonResponse
     */
    public function __invoke(AutocompleteRequest $request): JsonResponse
    {
        $query = $request->input('q');
        $limit = (int) $request->input('limit', 10);

        $suggestions = $this->searchService->autocomplete($query, $limit);

        return response()->json([
            'suggestions' => AutocompleteSuggestionResource::collection($suggestions),
        ]);
    }
}
