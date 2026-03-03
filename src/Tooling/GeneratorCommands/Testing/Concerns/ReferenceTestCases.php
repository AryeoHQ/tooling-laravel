<?php

declare(strict_types=1);

namespace Tooling\GeneratorCommands\Testing\Concerns;

use PHPUnit\Framework\Attributes\Test;

/**
 * @mixin \PHPUnit\Framework\TestCase
 * @mixin \Tooling\GeneratorCommands\Testing\Contracts\TestsReference
 */
trait ReferenceTestCases
{
    #[Test]
    public function it_derives_the_name(): void
    {
        $this->assertSame($this->expectedName, $this->subject->name->toString());
    }

    #[Test]
    public function it_derives_the_subdirectory(): void
    {
        $this->assertSame(
            $this->expectedSubdirectory,
            $this->subject->subdirectory?->toString(),
        );
    }

    #[Test]
    public function it_derives_the_fqcn(): void
    {
        $this->assertSame(
            $this->subject->namespace->toString().'\\'.$this->subject->name->toString(),
            $this->subject->fqcn->toString(),
        );
    }

    #[Test]
    public function it_derives_the_file_path(): void
    {
        $this->assertStringEndsWith(
            $this->subject->directory->toString().'/'.$this->expectedName.'.php',
            $this->subject->filePath->toString(),
        );
    }

    #[Test]
    public function it_derives_the_test_name(): void
    {
        $this->assertSame(
            $this->expectedName.'Test',
            $this->subject->test->name->toString(),
        );
    }

    #[Test]
    public function it_derives_the_test_file_path(): void
    {
        $this->assertStringEndsWith(
            $this->subject->directory->toString().'/'.$this->expectedName.'Test.php',
            $this->subject->test->filePath->toString(),
        );
    }
}
