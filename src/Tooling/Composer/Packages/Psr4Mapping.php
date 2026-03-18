<?php

declare(strict_types=1);

namespace Tooling\Composer\Packages;

use Illuminate\Support\Stringable;

final readonly class Psr4Mapping
{
    public Stringable $prefix;

    public Stringable $path;

    public function __construct(Stringable|string $prefix, Stringable|string $path)
    {
        $this->prefix = str($prefix)->start('\\')->finish('\\');
        $this->path = str($path)->finish('/');
    }
}
