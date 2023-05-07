<?php

namespace betterphp\Orm;

use Attribute;

require_once __DIR__.'/Constraint.php';

#[Attribute]
class AutoIncrement extends Constraint
{

}