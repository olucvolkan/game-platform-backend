<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\GameDetailResource;
use App\Services\GameService;
use Illuminate\Http\JsonResponse;

class GameController extends Controller
{
    public function __construct(
        protected GameService $gameService
    ) {}

    /**
     * Get detailed information about a single game.
     *
     * @param string $slug
     * @return GameDetailResource|JsonResponse
     */
    public function show(string $slug): GameDetailResource|JsonResponse
    {
        $game = $this->gameService->getBySlug($slug);

        if (!$game) {
            return response()->json([
                'error' => 'Game not found',
                'statusCode' => 404,
            ], 404);
        }

        return new GameDetailResource($game);
    }
}
