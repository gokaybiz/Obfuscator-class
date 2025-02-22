<?php

namespace Gokaybiz\Obfuscator;

final readonly class Config
{
    /**
     * Creates a new Config instance.
     *
     * @param string $key The key.
     * @param string $value The value.
     * @param string $function The function.
     * @param int $offset The offset.
     * @param string $salt The salt.
     */
    public function __construct(
        public string $key,
        public string $value,
        public string $function,
        public int $offset,
        public string $salt
    ) {}
}
