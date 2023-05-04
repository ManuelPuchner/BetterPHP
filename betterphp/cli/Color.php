<?php

// ansii color codes
namespace betterphp\cmd;
class Color
{
    const RED = "\033[0;31m";
    const GREEN = "\033[0;32m";
    const YELLOW = "\033[0;33m";
    const BLUE = "\033[0;34m";
    const PURPLE = "\033[0;35m";
    const CYAN = "\033[0;36m";
    const WHITE = "\033[0;37m";
    const RESET = "\033[0m";

    public static function get(string $text, string $color): string
    {
        return $color . $text . self::RESET;
    }
}