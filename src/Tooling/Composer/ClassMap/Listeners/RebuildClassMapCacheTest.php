<?php

declare(strict_types=1);

namespace Tooling\Composer\ClassMap\Listeners;

use Illuminate\Console\Events\CommandFinished;
use Illuminate\Support\Facades\Process;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;
use Tests\TestCase;

class RebuildClassMapCacheTest extends TestCase
{
    #[Test]
    public function it_starts_a_background_process_for_make_commands(): void
    {
        Process::fake();

        $this->dispatchCommandFinished('make:model');

        Process::assertRan(
            fn ($process) => str_contains($process->command, 'tooling:optimize')
        );
    }

    #[Test]
    public function it_does_not_start_a_process_for_non_make_commands(): void
    {
        Process::fake();

        $this->dispatchCommandFinished('migrate');

        Process::assertNothingRan();
    }

    #[Test]
    public function it_does_not_start_a_process_for_commands_containing_but_not_starting_with_make(): void
    {
        Process::fake();

        $this->dispatchCommandFinished('prefixed:make:fake');

        Process::assertNothingRan();
    }

    #[Test]
    public function the_background_process_runs_tooling_optimize_quietly(): void
    {
        Process::fake();

        $this->dispatchCommandFinished('make:fake');

        Process::assertRan(
            fn ($process) => str_contains($process->command, 'tooling:optimize --quiet')
        );
    }

    private function dispatchCommandFinished(string $command): void
    {
        rescue(fn () => event(
            new CommandFinished($command, new ArrayInput([]), new NullOutput, 0))
        );
    }
}
