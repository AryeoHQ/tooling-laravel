<?php

declare(strict_types=1);

namespace Tooling\Console\Commands;

use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Tooling\Composer\ClassMap\Cache;
use Tooling\Composer\ClassMap\Collectors\Untested;

class ToolingOptimizeTest extends TestCase
{
    #[Test]
    public function it_builds_the_classmap_cache(): void
    {
        Cache::fake();

        $this->artisan(ToolingOptimize::class)->assertSuccessful();

        $cache = resolve(Cache::class);

        $this->assertTrue($cache->has(Untested::class));
    }

    #[Test]
    public function it_outputs_collector_keys_as_tasks(): void
    {
        Cache::fake();

        $this->artisan(ToolingOptimize::class)
            ->assertSuccessful()
            ->expectsOutputToContain(Untested::class);
    }

    #[Test]
    public function it_suppresses_output_when_quiet(): void
    {
        Cache::fake();

        $this->artisan(ToolingOptimize::class, ['--quiet' => true])
            ->assertSuccessful();

        $cache = resolve(Cache::class);

        $this->assertTrue($cache->has(Untested::class));
    }
}
