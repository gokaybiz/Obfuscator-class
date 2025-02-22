<?php

namespace Gokaybiz\Obfuscator;

class Util
{
    /**
     * Pipes the value through the given functions.
     *
     * @param mixed $value The value to pipe.
     * @param callable ...$fns The functions to pipe through.
     * @return mixed The result of the last function.
     */
    public static function pipe(mixed $value, callable ...$fns): mixed
    {
        return array_reduce($fns, fn($acc, $fn) => $fn($acc), $value);
    }
}
