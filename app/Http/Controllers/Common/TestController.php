<?php

namespace App\Http\Controllers\Common;

use App\Utils\Helper;
use Illuminate\Http\Request;

class TestController
{
    public function test(Request $request)
    {
        $data = $request->all();
        dd($data);
        dd(Helper::createPassword("123456"));
    }
}
