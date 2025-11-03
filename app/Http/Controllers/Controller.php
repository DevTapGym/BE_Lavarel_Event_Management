<?php

namespace App\Http\Controllers;

use App\Traits\ApiResponse;
use App\Traits\PaginationResponse;

abstract class Controller
{
    use ApiResponse, PaginationResponse;
}
