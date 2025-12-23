<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Laravel\Scout\Searchable;

class Game extends Model
{
    use HasFactory, Searchable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'igdb_id',
        'slug',
        'title',
        'image',
        'price',
        'original_price',
        'discount',
        'platform',
        'region',
        'product_type',
        'has_cashback',
        'cashback_percent',
        'release_date',
        'developer',
        'publisher',
        'description',
        'popularity_score',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'price' => 'decimal:2',
        'original_price' => 'decimal:2',
        'discount' => 'integer',
        'has_cashback' => 'boolean',
        'cashback_percent' => 'integer',
        'release_date' => 'date',
        'popularity_score' => 'integer',
    ];

    /**
     * Get the genres for this game.
     */
    public function genres(): BelongsToMany
    {
        return $this->belongsToMany(Genre::class, 'game_genre');
    }

    /**
     * Get the screenshots for this game.
     */
    public function screenshots(): HasMany
    {
        return $this->hasMany(GameScreenshot::class)->orderBy('order');
    }

    /**
     * Get the indexable data array for the model.
     *
     * @return array<string, mixed>
     */
    public function toSearchableArray(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'developer' => $this->developer,
            'publisher' => $this->publisher,
            'slug' => $this->slug,
        ];
    }

    /**
     * Scope a query to filter by platform.
     */
    public function scopeByPlatform($query, ?string $platforms)
    {
        if (empty($platforms)) {
            return $query;
        }

        $platformList = array_map('trim', explode(',', $platforms));
        return $query->whereIn('platform', $platformList);
    }

    /**
     * Scope a query to filter by product type.
     */
    public function scopeByProductType($query, ?string $types)
    {
        if (empty($types)) {
            return $query;
        }

        $typeList = array_map('trim', explode(',', $types));
        return $query->whereIn('product_type', $typeList);
    }

    /**
     * Scope a query to filter by genres.
     */
    public function scopeByGenres($query, ?string $genres)
    {
        if (empty($genres)) {
            return $query;
        }

        $genreList = array_map('trim', explode(',', strtolower($genres)));
        return $query->whereHas('genres', function ($q) use ($genreList) {
            $q->whereIn('slug', $genreList);
        });
    }

    /**
     * Scope a query to filter by price range.
     */
    public function scopeByPriceRange($query, ?float $minPrice, ?float $maxPrice)
    {
        if ($minPrice !== null) {
            $query->where('price', '>=', $minPrice);
        }

        if ($maxPrice !== null) {
            $query->where('price', '<=', $maxPrice);
        }

        return $query;
    }

    /**
     * Scope a query to sort by specified option.
     */
    public function scopeSortBy($query, string $sort = 'popularity')
    {
        return match ($sort) {
            'price-asc' => $query->orderBy('price', 'asc'),
            'price-desc' => $query->orderBy('price', 'desc'),
            'newest' => $query->orderBy('release_date', 'desc'),
            'discount' => $query->orderBy('discount', 'desc'),
            default => $query->orderBy('popularity_score', 'desc'),
        };
    }
}
