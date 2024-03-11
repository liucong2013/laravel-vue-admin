<?php

namespace App\Http\Controllers\System;

use App\Http\Controllers\Controller;
use App\Http\Controllers\CustomController;
use App\Services\System\AuthorityService;

class AuthorityController extends CustomController
{
    protected $server;

    public function __construct(AuthorityService $server)
    {
        $this->server = $server;
    }
}
