<?php

namespace betterphp\utils\attributes;
use Attribute;


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