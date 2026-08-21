<?php

declare(strict_types=1);

namespace Tooling\PhpStan;

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
            Console\Commands\Analyze::class,
            Console\Commands\Bisect::class,
            Console\Commands\CacheClear::class,
            Console\Commands\Diagnose::class,
            Console\Commands\ParametersDump::class,
        ])->each(fn (string $class) => $this->assertTrue(
            $commands->contains(fn ($command) => $command instanceof $class),
            "{$class} was not registered.",
        ));
    }
}
