<?php

namespace betterphp\utils;
class Autoloader
{
    private static Autoloader $instance;

    private string $baseDir = '/var/www/';

    private static array $namespaceMap = [];


    private function __construct() {
        Autoloader::$namespaceMap = [
            'controller\\' => $this->baseDir . 'html/controller/',
            'model\\' => $this->baseDir . 'html/model/',
            'betterphp\\utils\\' => $this->baseDir . '/betterphp/utils/',
            'betterphp\\Orm\\' => $this->baseDir . '/betterphp/Orm/',
        ];
    }

    public static function load(): void
    {
        if (!isset(self::$instance)) {
            self::$instance = new Autoloader();
        }
        spl_autoload_register(function ($className) {

            // Check if the class belongs to a supported namespace
            foreach (self::$namespaceMap as $namespacePrefix => $directory) {
                if (str_starts_with($className, $namespacePrefix)) {
                    // Build the file path based on the namespace and class name
                    $relativePath = str_replace('\\', '/', substr($className, strlen($namespacePrefix))) . '.php';
                    $filePath = $directory . $relativePath;

                    // Require the class file if it exists
                    if (file_exists($filePath)) {
                        require_once $filePath;
                        return;
                    }
                }
            }
        });
    }

}