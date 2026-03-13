<?php

declare(strict_types=1);

namespace Tests\Tooling\GeneratorCommands;

use PHPUnit\Framework\Attributes\CoversTrait;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Tooling\Composer\Composer;
use Tooling\GeneratorCommands\References\Contracts\Reference;
use Tooling\GeneratorCommands\References\GenericClass;
use Tooling\GeneratorCommands\Testing\Concerns\CleansUpGeneratorCommands;

#[CoversTrait(CleansUpGeneratorCommands::class)]
class CleansUpGeneratorCommandsTest extends TestCase
{
    use CleansUpGeneratorCommands;

    public Reference $reference {
        get => new GenericClass(name: 'Invoice', baseNamespace: 'Workbench\\App\\Deep\\Nested');
    }

    #[Test]
    public function it_prunes_empty_directories_up_to_the_psr4_boundary(): void
    {
        $directory = $this->reference->directory->toString();

        $this->app['files']->ensureDirectoryExists($directory);
        $this->assertDirectoryExists($directory);

        $this->tearDownCleansUpGeneratorCommands();

        $this->assertDirectoryDoesNotExist($directory);
    }

    #[Test]
    public function it_does_not_prune_the_psr4_source_directory_itself(): void
    {
        $directory = $this->reference->directory->toString();
        $boundary = resolve(Composer::class)->baseDirectory->toString().'/workbench/app';

        $this->app['files']->ensureDirectoryExists($directory);

        $this->tearDownCleansUpGeneratorCommands();

        $this->assertDirectoryExists($boundary);
    }
}
