<?php

declare(strict_types=1);

namespace Tooling\GeneratorCommands\Testing\Concerns;

use Illuminate\Support\Facades\File;
use PHPUnit\Framework\Attributes\Test;
use Tooling\Composer\Composer;

/**
 * @mixin \Tests\TestCase
 */
trait RetrievesNamespaceTestCases
{
    #[Test]
    public function it_resolves_the_namespace_with_a_trailing_backslash(): void
    {
        Composer::fake();

        $this->artisan($this->command, $this->withNamespaceBackslashInput)->assertSuccessful();

        $this->assertTrue(File::exists($this->expectedFilePath));
    }

    #[Test]
    public function it_resolves_the_namespace_without_a_trailing_backslash(): void
    {
        Composer::fake();

        $this->artisan($this->command, $this->withoutNamespaceBackslashInput)->assertSuccessful();

        $this->assertTrue(File::exists($this->expectedFilePath));
    }

    #[Test]
    public function it_resolves_a_nested_namespace_under_a_known_prefix(): void
    {
        Composer::fake();

        $this->artisan($this->command, $this->withNestedNamespaceInput)->assertSuccessful();

        $this->assertTrue(File::exists($this->expectedNestedFilePath));
    }

    #[Test]
    public function it_resolves_namespace_when_psr4_path_is_an_array(): void
    {
        Composer::fake(['autoload' => ['psr-4' => [
            'App\\' => ['src/', 'src-extra/'],
        ]]]);

        $this->artisan($this->command, $this->baselineInput)->assertSuccessful();

        $this->assertTrue(File::exists($this->expectedFilePath));
    }
}
