<?php

declare(strict_types=1);

namespace Tooling\Pint\Console;

use Tooling\Pint\Console\Commands\Fixtures\DefaultCommand;

class Inspector extends \Tooling\Console\Inspectors\Inspector
{
    protected DefaultCommand $command;

    public function __construct(DefaultCommand $command)
    {
        $this->command = $command;
    }
}
