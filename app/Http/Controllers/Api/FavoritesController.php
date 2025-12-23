<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\GameResource;
use App\Models\Game;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class FavoritesController extends Controller
{
    /**
     * Get the authenticated user's favorite games.
     *
     * @param Request $request
     * @return AnonymousResourceCollection
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $favorites = $request->user()
            ->favorites()
            ->with('genres')
            ->orderByPivot('created_at', 'desc')
            ->get();

        return GameResource::collection($favorites);
    }

    /**
     * Add a game to the user's favorites.
     *
     * @param Request $request
     * @param int $gameId
     * @return JsonResponse
     */
    public function store(Request $request, int $gameId): JsonResponse
    {
        $game = Game::find($gameId);

        if (!$game) {
            return response()->json([
                'message' => 'Game not found',
            ], 404);
        }

        $user = $request->user();

        // Check if already in favorites
        if ($user->favorites()->where('game_id', $gameId)->exists()) {
            return response()->json([
                'message' => 'Game is already in your favorites',
            ], 409);
        }

        $user->favorites()->attach($gameId);

        return response()->json([
            'message' => 'Game added to favorites',
        ], 201);
    }

    /**
     * Remove a game from the user's favorites.
     *
     * @param Request $request
     * @param int $gameId
     * @return JsonResponse
     */
    public function destroy(Request $request, int $gameId): JsonResponse
    {
        $user = $request->user();

        // Check if game exists in favorites
        if (!$user->favorites()->where('game_id', $gameId)->exists()) {
            return response()->json([
                'message' => 'Game is not in your favorites',
            ], 404);
        }

        $user->favorites()->detach($gameId);

        return response()->json([
            'message' => 'Game removed from favorites',
        ]);
    }
}
