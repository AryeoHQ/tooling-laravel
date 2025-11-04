<?php

declare(strict_types=1);

namespace Tooling\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Foundation\Console\ConfigClearCommand;
use Illuminate\Foundation\Console\PackageDiscoverCommand;
use Symfony\Component\Console\Attribute\AsCommand;
use Tooling\Composer\Manifest;

#[AsCommand(name: 'tooling:discover', description: 'Rebuild the cached tooling manifest')]
class ToolingDiscover extends Command
{
    public function handle(Manifest $tooling): void
    {
        $this->callSilently(ConfigClearCommand::class);
        $this->callSilently(PackageDiscoverCommand::class);

        $this->components->info('Discovering tooling');

        collect((array) $tooling->loaded)
            ->keys()
            ->each(fn ($tool) => $this->components->task($tool))
            ->whenNotEmpty(fn () => $this->newLine());
    }
}
