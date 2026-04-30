<?php

declare(strict_types=1);

namespace Tooling\PhpStan\Console\Inspectors;

use PHPStan\Command\AnalyseCommand;

class Analyze extends \Tooling\Console\Inspectors\Inspector
{
    protected AnalyseCommand $command;

    public function __construct(AnalyseCommand $command)
    {
        $this->command = $command;
    }
}
