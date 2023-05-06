<?php

namespace betterphp\utils;
use Attribute;
use Exception;


#[Attribute(Attribute::TARGET_METHOD)]
class Route {

        private string $path;

        public function __construct(string $path) {
            $this->path = $path;
        }

        public function getPath(): string {
            return $this->path;
        }
}