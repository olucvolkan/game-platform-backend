<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GameResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'title' => $this->title,
            'image' => $this->image,
            'price' => (float) $this->price,
            'originalPrice' => (float) $this->original_price,
            'discount' => (int) $this->discount,
            'platform' => $this->platform,
            'region' => $this->region,
            'productType' => $this->product_type,
            'hasCashback' => (bool) $this->has_cashback,
            'cashbackPercent' => (int) $this->cashback_percent,
            'releaseDate' => $this->release_date?->format('Y-m-d'),
            'developer' => $this->developer,
            'publisher' => $this->publisher,
            'genres' => $this->whenLoaded('genres', function () {
                return $this->genres->pluck('name')->toArray();
            }, []),
        ];
    }
}
