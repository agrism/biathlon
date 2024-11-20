<?php

namespace App\Helpers;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class PaginatorHelper
{
    use InstanceTrait;

    public function get(array $data  = [],int $page = 1, int $perPage = 100): LengthAwarePaginator
    {
        $collection = Collection::make($data)->reverse();

        $currentPageItems = $collection->slice(($page - 1) * $perPage, $perPage)->values();

        return new LengthAwarePaginator(
            $currentPageItems, // Only the items for the current page
            $collection->count(), // Total number of items
            $perPage, // Items per page
            $page, // Current page
            ['path' => request()->url(), 'query' => request()->query()] // Pagination URL options
        );
    }
}
