<?php

declare(strict_types=1);

namespace Tooling\Composer\Plugins\Features;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Process\Factory;
use Illuminate\Process\PendingProcess;
use Tooling\Composer\ClassMapSource;
use Tooling\Composer\Composer;

class DiscoverTooling
{
    protected \Composer\Composer $composer;

    protected string $rootDirectory {
        get => $this->rootDirectory ??= dirname($this->composer->getConfig()->get('vendor-dir'));
    }

    protected PendingProcess $process {
        get => $this->process ??= new Factory()->newPendingProcess();
    }

    public function __construct(\Composer\Composer $composer)
    {
        $this->composer = $composer;
    }

    public function run(): void
    {
        match ($artisan = new Composer(new Filesystem, new ClassMapSource)->artisan()) {
            null => null,
            default => $this->process->path($this->rootDirectory)
                ->run("php $artisan tooling:discover -q && php $artisan tooling:optimize -q"),
        };
    }
}
