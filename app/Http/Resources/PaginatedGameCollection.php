<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class PaginatedGameCollection extends ResourceCollection
{
    /**
     * The resource that this resource collects.
     *
     * @var string
     */
    public $collects = GameResource::class;

    /**
     * Indicates if the collection should preserve its keys.
     *
     * @var bool
     */
    public $preserveKeys = false;

    /**
     * Transform the resource collection into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'data' => $this->collection,
            'meta' => [
                'total' => $this->resource->total(),
                'page' => $this->resource->currentPage(),
                'perPage' => $this->resource->perPage(),
                'lastPage' => $this->resource->lastPage(),
            ],
        ];
    }

    /**
     * Get any additional data that should be returned with the resource array.
     *
     * @param Request $request
     * @return array<string, mixed>
     */
    public function with(Request $request): array
    {
        return [];
    }

    /**
     * Customize the pagination information for the resource.
     *
     * @param  Request  $request
     * @param  array<string, mixed>  $paginated
     * @param  array<string, mixed>  $default
     * @return array<string, mixed>
     */
    public function paginationInformation(Request $request, array $paginated, array $default): array
    {
        return [];
    }
}
