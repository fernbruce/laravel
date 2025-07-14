<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class Admin
{
    /**
     * Handle an incoming request.
     *
     * @param  Request  $request
     * @param  Closure  $next
     * @param  string|null  $guard
     * @return mixed
     */
    public function handle($request, Closure $next, $a, $b)
    {
        echo "Admin Middleware: Checking access...\n";
        echo $a."\n";
        echo $b."\n";
        $return = $next($request);
        echo "\nAdmin Middleware: Access granted!\n";

        return $return;
    }
}
