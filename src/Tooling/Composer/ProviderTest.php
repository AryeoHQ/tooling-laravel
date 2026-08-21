<?php

declare(strict_types=1);

namespace Tooling\Composer;

use Illuminate\Console\Events\CommandFinished;
use Illuminate\Support\Facades\Event;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Tooling\Composer\ClassMap\Cache;

class ProviderTest extends TestCase
{
    #[Test]
    public function it_registers_composer_as_singleton(): void
    {
        $this->assertSame(app(Composer::class), app(Composer::class));
    }

    #[Test]
    public function it_registers_class_map_source_as_singleton(): void
    {
        $this->assertSame(app(ClassMapSource::class), app(ClassMapSource::class));
    }

    #[Test]
    public function it_registers_manifest_as_singleton(): void
    {
        $this->assertSame(app(Manifest::class), app(Manifest::class));
    }

    #[Test]
    public function it_registers_cache_as_singleton(): void
    {
        $this->assertSame(app(Cache::class), app(Cache::class));
    }

    #[Test]
    public function it_tags_classmap_collectors(): void
    {
        $tagged = iterator_to_array(app()->tagged('tooling.classmap.collectors'));

        $this->assertCount(2, $tagged);
    }

    #[Test]
    public function it_listens_for_command_finished(): void
    {
        $this->assertTrue(Event::hasListeners(CommandFinished::class));
    }
}
