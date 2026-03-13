<?php

declare(strict_types=1);

namespace Tooling\GeneratorCommands\References\Concerns;

use PHPUnit\Framework\Attributes\Test;

/**
 * @mixin \PHPUnit\Framework\TestCase
 * @mixin \Tooling\GeneratorCommands\Testing\Contracts\TestsReference
 */
trait ResolvesPathsTestCases
{
    #[Test]
    public function it_resolves_an_absolute_directory(): void
    {
        $this->assertTrue(
            $this->subject->directory->startsWith('/'),
            'Directory must be an absolute path.',
        );
    }

    #[Test]
    public function it_does_not_include_a_trailing_slash_in_directory(): void
    {
        $this->assertFalse(
            $this->subject->directory->endsWith('/'),
            'Directory must not end with a trailing slash.',
        );
    }

    #[Test]
    public function it_resolves_the_file_path_within_the_directory(): void
    {
        $this->assertStringStartsWith(
            $this->subject->directory->toString().'/',
            $this->subject->filePath->toString(),
        );
    }

    #[Test]
    public function it_appends_the_php_extension_to_the_file_path(): void
    {
        $this->assertStringEndsWith('.php', $this->subject->filePath->toString());
    }
}
