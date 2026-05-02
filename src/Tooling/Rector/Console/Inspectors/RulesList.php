<?php

declare(strict_types=1);

namespace Tooling\Rector\Console\Inspectors;

use Rector\Console\Command\ListRulesCommand;

class RulesList extends \Tooling\Console\Inspectors\Inspector
{
    protected ListRulesCommand $command;

    public function __construct(ListRulesCommand $command)
    {
        $this->command = $command;
    }
}
