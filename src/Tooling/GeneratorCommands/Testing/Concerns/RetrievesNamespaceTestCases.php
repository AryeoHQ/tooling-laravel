<?php

declare(strict_types=1);

namespace Tooling\GeneratorCommands\Testing\Concerns;

use PHPUnit\Framework\Attributes\Test;
use Tooling\Composer\Composer;
use Tooling\Composer\Packages\Package;

/**
 * @mixin \Tests\TestCase
 */
trait RetrievesNamespaceTestCases
{
    #[Test]
    public function it_resolves_the_namespace_with_a_trailing_backslash(): void
    {
        $this->artisan($this->command, $this->withNamespaceBackslashInput)->assertSuccessful();

        $this->assertFileExists($this->expectedFilePath);
    }

    #[Test]
    public function it_resolves_the_namespace_without_a_trailing_backslash(): void
    {
        $this->artisan($this->command, $this->withoutNamespaceBackslashInput)->assertSuccessful();

        $this->assertFileExists($this->expectedFilePath);
    }

    #[Test]
    public function it_resolves_a_nested_namespace_under_a_known_prefix(): void
    {
        $this->artisan($this->command, $this->withNestedNamespaceInput)->assertSuccessful();

        $this->assertFileExists($this->expectedNestedFilePath);
    }

    #[Test]
    public function it_resolves_namespace_when_psr4_path_is_an_array(): void
    {
        $composer = resolve(Composer::class);

        $data = json_decode(json_encode([
            'autoload' => [
                'psr-4' => [
                    'Workbench\\App\\' => ['workbench/app/', 'workbench/app-extra/'],
                ],
            ],
        ]));

        $composer->currentAsPackage = new Package($data);

        $this->artisan($this->command, $this->baselineInput)->assertSuccessful();

        $this->assertFileExists($this->expectedFilePath);
    }
}
