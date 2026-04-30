<?php

declare(strict_types=1);

namespace Tooling\PhpStan\Console\Inspectors;

use PHPStan\Command\DiagnoseCommand;

class Diagnose extends \Tooling\Console\Inspectors\Inspector
{
    protected DiagnoseCommand $command;

    public function __construct(DiagnoseCommand $command)
    {
        $this->command = $command;
    }
}
