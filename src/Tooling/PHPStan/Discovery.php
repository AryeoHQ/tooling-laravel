<?php

declare(strict_types=1);

namespace Tooling\PHPStan;

use Illuminate\Support\Collection;
use Tooling\Composer\Manifest;

final class Discovery
{
    protected Manifest $manifest { get => $this->manifest ??= new Manifest; }

    /** @var Collection<int, string> */
    public Collection $includes {
        get => $this->includes ??= collect((array) $this->manifest->phpstan)->filter(
            fn (string $path) => is_file($path)
        );
    }
}
