<?php
namespace App\Core;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class Router
{
    private $routes = [];

    public function addRoute(string $method, string $path, callable $handler)
    {
        $this->routes[] = [
            'method' => $method,
            'path' => $path,
            'handler' => $handler
        ];
    }

    public function handle(Request $request): Response
    {
        $path = $request->getPathInfo(); // Получаем путь из запроса
        $method = $request->getMethod(); // Получаем метод запроса (GET, POST и т.д.)

        foreach ($this->routes as $route) {
            if ($route['method'] === $method && $route['path'] === $path) {
                return $route['handler']($request); // Вызов обработчика маршрута
            }
        }

        return new Response('Not Found', Response::HTTP_NOT_FOUND);
    }
}