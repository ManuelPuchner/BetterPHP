<?php

namespace utils;
use Exception;

require_once 'RouteIF.php';

class Route implements RouteIF
{
    private static array $routes = [];

    /**
     * @throws Exception
     * @param string $uri
     * @param callable $callback function to be called when route is requested
     * @param string $method
     */
    private static function addRoute(string $uri, callable $callback, string $method)
    {
        // check if route already exists
        foreach (self::$routes as $route) {
            if ($route['uri'] === $uri && $route['method'] === $method) {
                throw new Exception('Route already exists');
            }
        }

        // else add route
        self::$routes[] = [
            'uri' => $uri,
            'callback' => $callback,
            'method' => $method
        ];
    }

    public static function get(string $uri, callable $callback)
    {
        self::addRoute($uri, $callback, 'GET');
    }

    public static function post(string $uri, callable $callback)
    {
        self::addRoute($uri, $callback, 'POST');
    }

    public static function put(string $uri, callable $callback)
    {
        self::addRoute($uri, $callback, 'PUT');
    }

    public static function patch(string $uri, callable $callback)
    {
        self::addRoute($uri, $callback, 'PATCH');
    }

    public static function delete(string $uri, callable $callback)
    {
        self::addRoute($uri, $callback, 'DELETE');
    }


    public static function getRoutes(): array
    {
        return self::$routes;
    }
}