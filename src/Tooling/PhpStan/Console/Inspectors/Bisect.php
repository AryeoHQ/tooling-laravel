<?php

declare(strict_types=1);

namespace Tooling\PhpStan\Console\Inspectors;

use PHPStan\Command\BisectCommand;

class Bisect extends \Tooling\Console\Inspectors\Inspector
{
    protected BisectCommand $command;

    public function __construct(BisectCommand $command)
    {
        $this->command = $command;
    }
}
