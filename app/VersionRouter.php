<?php
namespace App;

use Illuminate\Routing\Route;
use Illuminate\Routing\Router;
use Illuminate\Support\Str;

class VersionRouter extends Router{

    protected function findRoute($request)
    {
        $this->current = $route = $this->routes->match($request);
        $action = $route->getAction();
        foreach($route->parameters as $k=>$v){

            $action['uses'] = Str::replaceFirst('{'.$k.'}',Str::ucfirst($v),$action['controller']);

        }
        $route->setAction($action);
        $this->container->instance(Route::class, $route);
        return $route;
    }
}
