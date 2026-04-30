<?php

declare(strict_types=1);

namespace Tooling\PhpStan\Console\Inspectors;

use PHPStan\Command\DumpParametersCommand;

class ParametersDump extends \Tooling\Console\Inspectors\Inspector
{
    protected DumpParametersCommand $command;

    public function __construct(DumpParametersCommand $command)
    {
        $this->command = $command;
    }
}
