<?php

namespace betterphp\utils;

use Attribute;
use PDO;

require_once __DIR__ . '/DBConnection.php';

#[Attribute(Attribute::TARGET_CLASS)]
#[Injectable]
class Controller
{

}