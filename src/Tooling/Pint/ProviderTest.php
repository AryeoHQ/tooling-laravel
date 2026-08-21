<?php

declare(strict_types=1);

namespace Tooling\Pint;

use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ProviderTest extends TestCase
{
    #[Test]
    public function it_registers_commands(): void
    {
        $commands = collect($this->app->make(\Illuminate\Contracts\Console\Kernel::class)->all());

        collect([
            Console\Commands\CloneBaseCommand::class,
            Console\Commands\Pint::class,
        ])->each(fn (string $class) => $this->assertTrue(
            $commands->contains(fn ($command) => $command instanceof $class),
            "{$class} was not registered.",
        ));
    }
}
