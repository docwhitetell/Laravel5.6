<?php

namespace App\Http\Middleware;

use Closure;


class AccessControlAllowOrigin
{
    /**
     *
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $response = $next($request);
        $response->header('Access-Control-Allow-Origin', '*');
        $response->header('Access-Control-Allow-Headers', 'Origin, Accept, Content-Type, Cookie, Accept,X-CSRF-TOKEN, X-Requested-With, Authorization, Cache-Control, multipart/form-data, application/json');
        $response->header('Access-Control-Allow-Methods', 'GET, POST, PATCH, PUT, OPTIONS, DELETE');
        $response->header('Access-Control-Allow-Credentials', 'true');
        $response->header('Cache-Control', 'true');
        //$response->header('Content-Type', '*');
        return $response;
    }


}