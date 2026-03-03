<?php

declare(strict_types=1);

namespace Tooling\GeneratorCommands\Testing\Concerns;

use PHPUnit\Framework\Attributes\Test;

/**
 * @mixin \Tests\TestCase
 */
trait RetrievesNamespaceFromInputTestCases
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
}
