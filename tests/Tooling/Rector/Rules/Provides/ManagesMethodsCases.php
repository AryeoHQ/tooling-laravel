<?php

declare(strict_types=1);

namespace Tests\Tooling\Rector\Rules\Provides;

use PHPUnit\Framework\Attributes\Test;

trait ManagesMethodsCases
{
    #[Test]
    public function it_adds_a_method_to_a_class(): void
    {
        $node = $this->getClassNode($this->getFixturePath('ClassWithInterface.php'));

        $this->assertTrue($this->doesNotHaveMethod($node, 'newMethod'));

        $node = $this->addMethod($node, 'newMethod', 'void');

        $this->assertTrue($this->hasMethod($node, 'newMethod'));
    }

    #[Test]
    public function it_does_not_add_a_method_that_already_exists(): void
    {
        $node = $this->getClassNode($this->getFixturePath('ClassWithInterface.php'));

        $stmtCount = count($node->stmts);

        $node = $this->addMethod($node, 'classWithInterface', 'void');

        $this->assertCount($stmtCount, $node->stmts);
    }

    #[Test]
    public function it_adds_a_method_with_a_union_return_type(): void
    {
        $node = $this->getClassNode($this->getFixturePath('ClassWithInterface.php'));

        $node = $this->addMethod($node, 'unionMethod', 'int|bool');

        $this->assertTrue($this->hasMethod($node, 'unionMethod'));
    }

    #[Test]
    public function it_removes_a_method_from_a_class(): void
    {
        $node = $this->getClassNode($this->getFixturePath('ClassWithInterface.php'));

        $this->assertTrue($this->hasMethod($node, 'classWithInterface'));

        $node = $this->removeMethod($node, 'classWithInterface');

        $this->assertTrue($this->doesNotHaveMethod($node, 'classWithInterface'));
    }

    #[Test]
    public function it_does_not_modify_a_class_when_removing_a_method_it_does_not_have(): void
    {
        $node = $this->getClassNode($this->getFixturePath('ClassWithInterface.php'));

        $stmtCount = count($node->stmts);

        $node = $this->removeMethod($node, 'nonExistentMethod');

        $this->assertCount($stmtCount, $node->stmts);
    }

    #[Test]
    public function it_adds_a_method_to_an_enum(): void
    {
        $node = $this->getEnumNode($this->getFixturePath('EnumWithMethod.php'));

        $this->assertTrue($this->doesNotHaveMethod($node, 'newMethod'));

        $node = $this->addMethod($node, 'newMethod', 'void');

        $this->assertTrue($this->hasMethod($node, 'newMethod'));
    }

    #[Test]
    public function it_removes_a_method_from_an_enum(): void
    {
        $node = $this->getEnumNode($this->getFixturePath('EnumWithMethod.php'));

        $this->assertTrue($this->hasMethod($node, 'enumMethod'));

        $node = $this->removeMethod($node, 'enumMethod');

        $this->assertTrue($this->doesNotHaveMethod($node, 'enumMethod'));
    }

    #[Test]
    public function it_adds_a_method_to_a_trait(): void
    {
        $node = $this->getTraitNode($this->getFixturePath('TraitWithoutTrait.php'));

        $this->assertTrue($this->doesNotHaveMethod($node, 'newMethod'));

        $node = $this->addMethod($node, 'newMethod', 'void');

        $this->assertTrue($this->hasMethod($node, 'newMethod'));
    }

    #[Test]
    public function it_removes_a_method_from_a_trait(): void
    {
        $node = $this->getTraitNode($this->getFixturePath('TraitWithoutTrait.php'));

        $this->assertTrue($this->hasMethod($node, 'something'));

        $node = $this->removeMethod($node, 'something');

        $this->assertTrue($this->doesNotHaveMethod($node, 'something'));
    }
}
