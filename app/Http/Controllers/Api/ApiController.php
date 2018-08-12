<?php

namespace App\Http\Controllers\Api;

use Illuminate\Foundation\Auth\AuthenticatesUsers;
use App\User;
use App\Http\Controllers\Controller;

class ApiController extends Controller
{
    // 其他通用的Api帮助函数
    use AuthenticatesUsers;

}
