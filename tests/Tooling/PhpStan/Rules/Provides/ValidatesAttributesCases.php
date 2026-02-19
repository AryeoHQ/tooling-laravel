<?php

declare(strict_types=1);

namespace Tests\Tooling\PhpStan\Rules\Provides;

use PHPUnit\Framework\Attributes\Test;
use Tests\Fixtures\Tooling\SomeAttribute;

trait ValidatesAttributesCases
{
    #[Test]
    public function it_detects_a_class_has_an_attribute(): void
    {
        $node = $this->getClassNode($this->getFixturePath('ClassWithAttribute.php'));

        $this->assertTrue($this->hasAttribute($node, SomeAttribute::class));
    }

    #[Test]
    public function it_detects_a_class_does_not_have_an_attribute(): void
    {
        $node = $this->getClassNode($this->getFixturePath('ClassWithInterface.php'));

        $this->assertFalse($this->hasAttribute($node, SomeAttribute::class));
    }

    #[Test]
    public function it_detects_a_class_does_not_have_an_attribute_via_negation(): void
    {
        $node = $this->getClassNode($this->getFixturePath('ClassWithInterface.php'));

        $this->assertTrue($this->doesNotHaveAttribute($node, SomeAttribute::class));
    }
}
