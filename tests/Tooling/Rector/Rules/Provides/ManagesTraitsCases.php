<?php

declare(strict_types=1);

namespace Tests\Tooling\Rector\Rules\Provides;

use PhpParser\Node\Stmt\TraitUse;
use PHPUnit\Framework\Attributes\Test;
use Tests\Fixtures\Tooling\Concern;
use Tests\Fixtures\Tooling\ParentConcern;

trait ManagesTraitsCases
{
    #[Test]
    public function it_adds_a_trait_to_a_class(): void
    {
        $node = $this->getClassNode($this->getFixturePath('ClassWithInterface.php'));

        $this->assertFalse($this->inherits($node, Concern::class));

        $node = $this->addTrait($node, Concern::class);

        $this->assertTrue($this->inherits($node, Concern::class));
    }

    #[Test]
    public function it_does_not_add_a_trait_that_is_already_used(): void
    {
        $node = $this->getClassNode($this->getFixturePath('ClassWithTrait.php'));

        $traitUseCount = $this->countTraitUseStatements($node);

        $node = $this->addTrait($node, Concern::class);

        $this->assertSame($traitUseCount, $this->countTraitUseStatements($node));
    }

    #[Test]
    public function it_removes_a_trait_from_a_class(): void
    {
        $node = $this->getClassNode($this->getFixturePath('ClassWithTrait.php'));

        $this->assertTrue($this->inherits($node, Concern::class));

        $node = $this->removeTrait($node, Concern::class);

        $this->assertFalse($this->inherits($node, Concern::class));
    }

    #[Test]
    public function it_does_not_modify_a_class_when_removing_a_trait_it_does_not_use(): void
    {
        $node = $this->getClassNode($this->getFixturePath('ClassWithInterface.php'));

        $stmtCount = count($node->stmts);

        $node = $this->removeTrait($node, ParentConcern::class);

        $this->assertCount($stmtCount, $node->stmts);
    }

    #[Test]
    public function it_adds_a_trait_to_an_enum(): void
    {
        $node = $this->getEnumNode($this->getFixturePath('EnumWithInterface.php'));

        $this->assertFalse($this->inherits($node, Concern::class));

        $node = $this->addTrait($node, Concern::class);

        $this->assertTrue($this->inherits($node, Concern::class));
    }

    #[Test]
    public function it_does_not_add_a_trait_that_is_already_used_on_an_enum(): void
    {
        $node = $this->getEnumNode($this->getFixturePath('EnumWithTrait.php'));

        $traitUseCount = $this->countTraitUseStatements($node);

        $node = $this->addTrait($node, Concern::class);

        $this->assertSame($traitUseCount, $this->countTraitUseStatements($node));
    }

    #[Test]
    public function it_removes_a_trait_from_an_enum(): void
    {
        $node = $this->getEnumNode($this->getFixturePath('EnumWithTrait.php'));

        $this->assertTrue($this->inherits($node, Concern::class));

        $node = $this->removeTrait($node, Concern::class);

        $this->assertFalse($this->inherits($node, Concern::class));
    }

    #[Test]
    public function it_adds_a_trait_to_a_trait(): void
    {
        $node = $this->getTraitNode($this->getFixturePath('TraitWithoutTrait.php'));

        $this->assertFalse($this->inherits($node, Concern::class));

        $node = $this->addTrait($node, Concern::class);

        $this->assertTrue($this->inherits($node, Concern::class));
    }

    #[Test]
    public function it_does_not_add_a_trait_that_is_already_used_on_a_trait(): void
    {
        $node = $this->getTraitNode($this->getFixturePath('TraitWithTrait.php'));

        $traitUseCount = $this->countTraitUseStatements($node);

        $node = $this->addTrait($node, Concern::class);

        $this->assertSame($traitUseCount, $this->countTraitUseStatements($node));
    }

    #[Test]
    public function it_removes_a_trait_from_a_trait(): void
    {
        $node = $this->getTraitNode($this->getFixturePath('TraitWithTrait.php'));

        $this->assertTrue($this->inherits($node, Concern::class));

        $node = $this->removeTrait($node, Concern::class);

        $this->assertFalse($this->inherits($node, Concern::class));
    }

    #[Test]
    public function it_does_not_modify_an_enum_when_removing_a_trait_it_does_not_use(): void
    {
        $node = $this->getEnumNode($this->getFixturePath('EnumWithInterface.php'));

        $stmtCount = count($node->stmts);

        $node = $this->removeTrait($node, ParentConcern::class);

        $this->assertCount($stmtCount, $node->stmts);
    }

    #[Test]
    public function it_does_not_modify_a_trait_when_removing_a_trait_it_does_not_use(): void
    {
        $node = $this->getTraitNode($this->getFixturePath('TraitWithoutTrait.php'));

        $stmtCount = count($node->stmts);

        $node = $this->removeTrait($node, ParentConcern::class);

        $this->assertCount($stmtCount, $node->stmts);
    }

    private function countTraitUseStatements(mixed $node): int
    {
        return count(array_filter($node->stmts, fn ($stmt) => $stmt instanceof TraitUse));
    }
}
