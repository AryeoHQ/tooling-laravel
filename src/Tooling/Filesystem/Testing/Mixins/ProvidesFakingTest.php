<?php

declare(strict_types=1);

namespace Tooling\Filesystem\Testing\Mixins;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\File;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Tooling\Filesystem\Testing\FilesystemFake;

#[CoversClass(ProvidesFaking::class)]
class ProvidesFakingTest extends TestCase
{
    #[Test]
    public function it_swaps_the_file_facade_root(): void
    {
        File::fake('/fake/*');

        $this->assertInstanceOf(FilesystemFake::class, File::getFacadeRoot());
    }

    #[Test]
    public function it_registers_container_bindings(): void
    {
        File::fake('/fake/*');

        $this->assertInstanceOf(FilesystemFake::class, app('files'));
        $this->assertInstanceOf(FilesystemFake::class, app(FilesystemFake::class));
        $this->assertInstanceOf(FilesystemFake::class, app(Filesystem::class));
    }

    #[Test]
    public function it_reuses_existing_fake_when_adding_paths(): void
    {
        $first = File::fake('/a/*');
        $second = File::fake('/b/*');

        $this->assertSame($first, $second);
    }
}
