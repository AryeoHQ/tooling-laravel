<?php

declare(strict_types=1);

namespace Tooling\GeneratorCommands\References\Concerns;

use PHPUnit\Framework\Attributes\Test;
use Tooling\GeneratorCommands\References\GenericClass;

/**
 * @mixin \PHPUnit\Framework\TestCase
 * @mixin \Tooling\GeneratorCommands\Testing\Contracts\TestsReference
 */
trait ManagesNamespaceTestCases
{
    #[Test]
    public function it_prepends_a_leading_backslash_to_base_namespace(): void
    {
        $this->assertTrue(
            $this->subject->baseNamespace->startsWith('\\'),
            'Base namespace must start with a leading backslash.',
        );
    }

    #[Test]
    public function it_strips_trailing_backslash_from_base_namespace(): void
    {
        $this->assertFalse(
            $this->subject->baseNamespace->endsWith('\\'),
            'Base namespace must not end with a trailing backslash.',
        );
    }

    #[Test]
    public function it_normalizes_all_base_namespace_input_formats(): void
    {
        $expected = '\\Foo';

        $this->assertSame($expected, (new GenericClass('X', 'Foo'))->baseNamespace->toString());
        $this->assertSame($expected, (new GenericClass('X', '\\Foo'))->baseNamespace->toString());
        $this->assertSame($expected, (new GenericClass('X', 'Foo\\'))->baseNamespace->toString());
        $this->assertSame($expected, (new GenericClass('X', '\\Foo\\'))->baseNamespace->toString());
    }
}
