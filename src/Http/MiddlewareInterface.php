<?php

namespace App\Http;

use App\Core\Request;
use App\Core\Response;

interface MiddlewareInterface
{
    public function handle(Request $request, callable $next): Response;
}