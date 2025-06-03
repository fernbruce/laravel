<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Log\Logger;
use Illuminate\Support\Facades\Log;

class Benchmark
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next,$a,$b)
    {
             echo "\nBenchmarking started at: " . now() . "\n"; 
             echo $a."\n";
             echo $b."\n";
             $response = $next($request);
             echo "\nBenchmarking ended at: " . now() . "\n";
             return $response;
    }
}
