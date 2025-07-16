<?php

namespace Outboard\Di\Enums;

enum Scope
{
    case Prototype;
    case Singleton;
    case Request;
    case Session;
}
