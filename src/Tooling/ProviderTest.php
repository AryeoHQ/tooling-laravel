<?php

declare(strict_types=1);

namespace Tooling;

use Illuminate\Filesystem\Filesystem;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ProviderTest extends TestCase
{
    #[Test]
    public function it_registers_sub_providers(): void
    {
        collect([
            Composer\Provider::class,
            PhpStan\Provider::class,
            Rector\Provider::class,
            Pint\Provider::class,
        ])->each(fn (string $class) => $this->assertInstanceOf(
            $class,
            app()->getProvider($class),
        ));
    }

    #[Test]
    public function it_merges_config(): void
    {
        $this->assertIsArray(config('tooling'));
    }

    #[Test]
    public function it_registers_filesystem_fake_macro(): void
    {
        $this->assertTrue(Filesystem::hasMacro('fake'));
    }

    #[Test]
    public function it_registers_shared_commands(): void
    {
        $commands = collect($this->app->make(\Illuminate\Contracts\Console\Kernel::class)->all());

        collect([
            Console\Commands\ToolingDiscover::class,
            Console\Commands\ToolingOptimize::class,
            GeneratorCommands\MakeTestClass::class,
        ])->each(fn (string $class) => $this->assertTrue(
            $commands->contains(fn ($command) => $command instanceof $class),
            "{$class} was not registered.",
        ));
    }
}
