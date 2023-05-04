<?php

namespace betterphp\utils;

interface RouteIF
{
    public static function get(string $uri, callable $callback);
    public static function post(string $uri, callable $callback);
    public static function put(string $uri, callable $callback);
    public static function patch(string $uri, callable $callback);
    public static function delete(string $uri, callable $callback);
}