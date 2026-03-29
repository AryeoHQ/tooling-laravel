<?php

declare(strict_types=1);

namespace Tooling\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Attribute\AsCommand;
use Tooling\Composer\ClassMap\Cache;

#[AsCommand(name: 'tooling:optimize', description: 'Rebuild the cached classmap discovery data')]
class ToolingOptimize extends Command
{
    public function handle(Cache $cache): void
    {
        if (! $this->option('quiet')) {
            $this->components->info('Optimizing classmap cache');
        }

        $cache->build();

        if (! $this->option('quiet')) {
            collect($cache->loaded)
                ->keys()
                ->each(fn (string $key) => $this->components->task($key))
                ->whenNotEmpty(fn () => $this->newLine());
        }
    }
}
