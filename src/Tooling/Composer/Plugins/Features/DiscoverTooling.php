<?php

declare(strict_types=1);

namespace Tooling\Composer\Plugins\Features;

use Composer\Composer;
use Illuminate\Process\Factory;
use Illuminate\Process\PendingProcess;

class DiscoverTooling
{
    protected Composer $composer;

    protected string $rootDirectory { get => $this->rootDirectory ??= dirname($this->composer->getConfig()->get('vendor-dir')); }

    protected PendingProcess $process { get => $this->process ??= new Factory()->newPendingProcess(); }

    public function __construct(Composer $composer)
    {
        $this->composer = $composer;
    }

    public function run(): void
    {
        if (! $artisan = $this->artisan()) {
            return;
        }

        $this->process->path($this->rootDirectory)->run("php $artisan tooling:discover -q");
    }

    private function artisan(): null|string
    {
        return collect(['/artisan', '/vendor/bin/testbench'])->map(
            fn (string $path): string => $this->rootDirectory.$path
        )->filter(
            fn (string $path): bool => file_exists($path)
        )->first();
    }
}
