<?php

declare(strict_types=1);

namespace Tooling\Rector\Console;

use Rector\Console\Command\ProcessCommand;
use Tooling\Console\Inspectors\Attributes\Conflicts;

#[Conflicts\Shortcuts(list: ['n'])]
class Inspector extends \Tooling\Console\Inspectors\Inspector
{
    protected ProcessCommand $command;

    public function __construct(ProcessCommand $command)
    {
        $this->command = $command;
    }
}
