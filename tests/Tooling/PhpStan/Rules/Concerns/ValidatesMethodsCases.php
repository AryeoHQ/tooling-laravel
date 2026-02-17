<?php

declare(strict_types=1);

namespace Tests\Tooling\PhpStan\Rules\Concerns;

use PHPUnit\Framework\Attributes\Test;

trait ValidatesMethodsCases
{
    #[Test]
    public function it_detects_a_method_defined_directly_on_the_class(): void
    {
        $node = $this->getClassNode($this->getFixturePath('ClassWithInterface.php'));

        $this->assertTrue($this->hasMethod($node, 'classWithInterface'));
    }

    #[Test]
    public function it_detects_a_class_does_not_have_an_undefined_method(): void
    {
        $node = $this->getClassNode($this->getFixturePath('ClassWithInterface.php'));

        $this->assertTrue($this->doesNotHaveMethod($node, 'nonExistentMethod'));
    }

    #[Test]
    public function it_detects_a_method_inherited_via_extends(): void
    {
        $node = $this->getClassNodeWithScope($this->getFixturePath('ClassWithExtends.php'));

        $this->assertTrue($this->hasMethod($node, 'parentClass'));
    }

    #[Test]
    public function it_detects_a_method_inherited_via_direct_trait_use(): void
    {
        $node = $this->getClassNodeWithScope($this->getFixturePath('ClassWithTrait.php'));

        $this->assertTrue($this->hasMethod($node, 'concern'));
    }

    #[Test]
    public function it_detects_a_method_inherited_via_parents_trait(): void
    {
        $node = $this->getClassNodeWithScope($this->getFixturePath('ClassWithExtends.php'));

        $this->assertTrue($this->hasMethod($node, 'parentConcern'));
    }

    #[Test]
    public function it_detects_a_method_inherited_via_trait_of_trait(): void
    {
        $node = $this->getClassNodeWithScope($this->getFixturePath('ClassWithChildTrait.php'));

        $this->assertTrue($this->hasMethod($node, 'parentConcern'));
    }

    #[Test]
    public function it_detects_a_class_does_not_have_a_method_from_an_unrelated_class(): void
    {
        $node = $this->getClassNodeWithScope($this->getFixturePath('ClassWithInterface.php'));

        $this->assertTrue($this->doesNotHaveMethod($node, 'parentClass'));
    }
}
