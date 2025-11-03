<?php

namespace App\Traits;

use Illuminate\Pagination\LengthAwarePaginator;

trait PaginationResponse
{
    protected function paginateResponse(LengthAwarePaginator $paginator)
    {
        $meta = [
            'page' => $paginator->currentPage(),
            'pageSize' => $paginator->perPage(),
            'pages' => $paginator->lastPage(),
            'total' => $paginator->total()
        ];

        return [
            'meta' => $meta,
            'result' => $paginator->items()
        ];
    }
}
