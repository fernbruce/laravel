<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class Benchmark
{
    /**
     * Handle an incoming request.
     *
     * @param  Request  $request
     * @param  Closure  $next
     * @param $a
     * @param $b
     * @return mixed
     */
//    public function handle($request, Closure $next, $a, $b)
//    {
//        echo "\nBenchmarking started at: ".now()."\n";
//        echo $a."\n";
//        echo $b."\n";
//        $response = $next($request);
//        echo "\nBenchmarking ended at: ".now()."\n";
//        return $response;
//    }
    public function handle($request, Closure $next,$a,$b)
    {
        echo "benchmark-前置{$a}-{$b}<br>";
        $response = $next($request);
        echo "<br>benchmark-后置<br>";
        return $response;
    }
}
