<?php

declare(strict_types=1);

namespace Tooling\Rector;

use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ProviderTest extends TestCase
{
    #[Test]
    public function it_registers_commands(): void
    {
        $commands = collect($this->app->make(\Illuminate\Contracts\Console\Kernel::class)->all());

        collect([
            Console\Commands\Make\MakeRule::class,
            Console\Commands\RulesList::class,
            Console\Commands\Process::class,
        ])->each(fn (string $class) => $this->assertTrue(
            $commands->contains(fn ($command) => $command instanceof $class),
            "{$class} was not registered.",
        ));
    }

    #[Test]
    public function it_registers_views(): void
    {
        /** @var \Illuminate\View\FileViewFinder $finder */
        $finder = app('view')->getFinder();

        $this->assertArrayHasKey('tooling.rector.rules.samples', $finder->getHints());
    }
}
