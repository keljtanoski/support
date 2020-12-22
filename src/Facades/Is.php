<?php

namespace Helldar\Support\Facades;

use Helldar\Support\Services\Reflection;
use ReflectionClass;

final class Is
{
    public static function object($value): bool
    {
        return is_object($value);
    }

    public static function string($value): bool
    {
        return is_string($value);
    }

    public static function contract($value): bool
    {
        $class = Instance::classname($value);

        return Reflection::resolve($value)->isInterface() || interface_exists($class);
    }

    public static function reflectionClass($class): bool
    {
        return $class instanceof ReflectionClass;
    }
}
