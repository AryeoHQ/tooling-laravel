<?php

declare(strict_types=1);

namespace Tooling\GeneratorCommands\References;

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
    public function it_enforces_the_namespace_invariant(): void
    {
        $this->assertTrue(
            $this->subject->namespace->startsWith('\\'),
            'Namespace must start with a leading backslash.',
        );

        $this->assertFalse(
            $this->subject->namespace->endsWith('\\'),
            'Namespace must not end with a trailing backslash.',
        );
    }
}
