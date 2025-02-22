<?php
/* CONFIG */
final readonly class Config
{
    public function __construct(
        public string $key,
        public string $value,
        public string $function,
        public int $offset,
        public string $salt
    ) {}
}
