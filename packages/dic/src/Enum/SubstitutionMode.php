<?php

namespace Outboard\Di\Enum;

enum SubstitutionMode
{
    case Callable;
    case Raw;
    case Constructor;
}
