<?php


namespace App;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class Router
{
    private array $routes = [];

    public function add(string $route, Controller $controller, string $method): void
    {
        $this->routes[$route] = [
            'controller' => $controller,
            'method' => $method,
        ];
    }

    public function execute(Request $request):Response
    {
        foreach ($this->routes as $route => $action) {
            if(preg_match($route, $request->getPathInfo(), $matches)){
                array_shift($matches);
                $matches[] = $request;
                return $action['controller']->{$action['method']}(...$matches);
            }
        }
        return new Response('', Response::HTTP_NOT_FOUND);
    }
}











