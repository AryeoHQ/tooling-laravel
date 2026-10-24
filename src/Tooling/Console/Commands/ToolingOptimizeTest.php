<?php

declare(strict_types=1);

namespace Tooling\Console\Commands;

use Illuminate\Support\Facades\File;
use PHPUnit\Framework\Attributes\Test;
use Tests\Fixtures\Tooling\ParentClass;
use Tests\TestCase;
use Tooling\Composer\ClassMap\Cache;
use Tooling\Composer\ClassMap\Collectors\Untested;
use Tooling\Composer\ClassMapSource;

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
    public function it_writes_the_cache_as_unkeyed_short_arrays(): void
    {
        ClassMapSource::fake()->merge([ParentClass::class => '/fake/src/ParentClass.php']);
        $cache = Cache::fake();

        $this->artisan(ToolingOptimize::class)->assertSuccessful();

        $contents = File::get($cache->cachePath);

        $this->assertStringStartsWith("<?php\n\nreturn [\n", $contents);
        $this->assertStringContainsString("    '".addslashes(Untested::class)."' => [\n        '".addslashes(ParentClass::class)."',\n    ],\n", $contents);
        $this->assertStringEndsWith("];\n", $contents);
        $this->assertDoesNotMatchRegularExpression('/^\s*\d+ =>/m', $contents);
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
