<?php

namespace App\Http\Controllers;

use App\Libraires\ApiResponse;
use App\Libraires\ToolsResponse;
use Illuminate\Http\Request;
use Auth;

class ApiController extends Controller
{
    use ApiResponse,ToolsResponse;

    protected $auth;

    public function __construct(Auth $auth) {
        $this->auth = $auth;
    }
}