<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\GameListRequest;
use App\Http\Resources\PaginatedGameCollection;
use App\Services\GameService;
use Illuminate\Http\JsonResponse;

class GameListController extends Controller
{
    public function __construct(
        protected GameService $gameService
    ) {}

    /**
     * Get paginated list of games with optional filtering and search.
     *
     * @param GameListRequest $request
     * @return PaginatedGameCollection
     */
    public function __invoke(GameListRequest $request): PaginatedGameCollection
    {
        $filters = [
            'page' => (int) $request->input('page', 1),
            'perPage' => (int) $request->input('perPage', 20),
            'search' => $request->input('search'),
            'sort' => $request->input('sort', 'popularity'),
            'minPrice' => $request->input('minPrice'),
            'maxPrice' => $request->input('maxPrice'),
            'types' => $request->input('types'),
            'platforms' => $request->input('platforms'),
            'genres' => $request->input('genres'),
        ];

        $games = $this->gameService->getFilteredGames($filters);

        return new PaginatedGameCollection($games);
    }
}
