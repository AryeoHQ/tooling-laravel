<?php

declare(strict_types=1);

namespace Tooling\Composer\Packages;

final readonly class Psr4Mapping
{
    public string $prefix;

    public string $path;

    public function __construct(string $prefix, string $path)
    {
        $this->prefix = $prefix;
        $this->path = $path;
    }
}
