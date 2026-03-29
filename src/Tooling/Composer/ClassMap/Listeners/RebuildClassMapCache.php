<?php

declare(strict_types=1);

namespace Tooling\Composer\ClassMap\Listeners;

use Illuminate\Console\Events\CommandFinished;
use Illuminate\Support\Facades\Process;
use Tooling\Composer\Composer;

class RebuildClassMapCache
{
    public function handle(CommandFinished $event): void
    {
        if (! str_starts_with($event->command, 'make:')) {
            return;
        }

        once(function (): void {
            match ($artisan = resolve(Composer::class)->artisan()) {
                null => null,
                default => Process::path(base_path())
                    ->options(['create_new_console' => true])
                    ->start("php {$artisan} tooling:optimize --quiet"),
            };
        });
    }
}
