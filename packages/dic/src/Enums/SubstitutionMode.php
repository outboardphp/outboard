<?php

namespace Outboard\Di\Enums;

enum SubstitutionMode
{
    case Callable;
    case Raw;
    case Constructor;
}
