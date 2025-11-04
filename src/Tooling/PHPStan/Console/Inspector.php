<?php

declare(strict_types=1);

namespace Tooling\PHPStan\Console;

use PHPStan\Command\AnalyseCommand;

class Inspector extends \Tooling\Console\Inspectors\Inspector
{
    protected AnalyseCommand $command;

    public function __construct(AnalyseCommand $command)
    {
        $this->command = $command;
    }
}
