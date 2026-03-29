<?php

namespace Outboard\Di\Enum;

enum Scope
{
    case Prototype;
    case Singleton;
    case Request;
    case Session;
}
