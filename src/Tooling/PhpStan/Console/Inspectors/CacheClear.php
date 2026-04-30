<?php

declare(strict_types=1);

namespace Tooling\PhpStan\Console\Inspectors;

use PHPStan\Command\ClearResultCacheCommand;

class CacheClear extends \Tooling\Console\Inspectors\Inspector
{
    protected ClearResultCacheCommand $command;

    public function __construct(ClearResultCacheCommand $command)
    {
        $this->command = $command;
    }
}
