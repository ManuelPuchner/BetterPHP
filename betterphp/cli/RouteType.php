<?php

namespace betterphp\cli;

enum RouteType
{
    case NORMAL;
    case PATH_PARAM;
    case QUERY_PARAM;
    case BODY_PARAM;
}
