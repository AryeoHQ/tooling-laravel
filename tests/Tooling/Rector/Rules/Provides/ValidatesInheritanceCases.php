<?php

declare(strict_types=1);

namespace Tests\Tooling\Rector\Rules\Provides;

use PHPUnit\Framework\Attributes\Test;
use Tests\Fixtures\Tooling\Concern;
use Tests\Fixtures\Tooling\Contract;
use Tests\Fixtures\Tooling\ParentClass;
use Tests\Fixtures\Tooling\ParentConcern;
use Tests\Fixtures\Tooling\ParentContract;

trait ValidatesInheritanceCases
{
    #[Test]
    public function it_detects_a_class_directly_extends_another(): void
    {
        $node = $this->getClassNode($this->getFixturePath('ClassWithExtends.php'));

        $this->assertTrue($this->inherits($node, ParentClass::class));
    }

    #[Test]
    public function it_detects_a_class_does_not_directly_extend_an_unrelated_class(): void
    {
        $node = $this->getClassNode($this->getFixturePath('ClassWithInterface.php'));

        $this->assertFalse($this->inherits($node, ParentClass::class));
    }

    #[Test]
    public function it_detects_a_class_directly_implements_an_interface(): void
    {
        $node = $this->getClassNode($this->getFixturePath('ClassWithInterface.php'));

        $this->assertTrue($this->inherits($node, Contract::class));
    }

    #[Test]
    public function it_detects_a_class_does_not_directly_implement_an_unrelated_interface(): void
    {
        $node = $this->getClassNode($this->getFixturePath('ClassWithTrait.php'));

        $this->assertFalse($this->inherits($node, Contract::class));
    }

    #[Test]
    public function it_detects_a_class_directly_uses_a_trait(): void
    {
        $node = $this->getClassNode($this->getFixturePath('ClassWithTrait.php'));

        $this->assertTrue($this->inherits($node, Concern::class));
    }

    #[Test]
    public function it_detects_a_class_does_not_directly_use_an_unrelated_trait(): void
    {
        $node = $this->getClassNode($this->getFixturePath('ClassWithInterface.php'));

        $this->assertFalse($this->inherits($node, Concern::class));
    }

    #[Test]
    public function it_detects_a_class_indirectly_extends_a_grandparent(): void
    {
        $node = $this->getClassNodeWithScope($this->getFixturePath('ClassWithExtends.php'));

        $this->assertTrue($this->inherits($node, ParentClass::class));
    }

    #[Test]
    public function it_detects_a_class_indirectly_implements_an_interface_via_parent(): void
    {
        $node = $this->getClassNodeWithScope($this->getFixturePath('ClassWithExtends.php'));

        $this->assertTrue($this->inherits($node, ParentContract::class));
    }

    #[Test]
    public function it_detects_a_class_indirectly_implements_a_parent_interface(): void
    {
        $node = $this->getClassNodeWithScope($this->getFixturePath('ClassWithChildInterface.php'));

        $this->assertTrue($this->inherits($node, ParentContract::class));
    }

    #[Test]
    public function it_detects_a_class_indirectly_uses_a_trait_via_parent(): void
    {
        $node = $this->getClassNodeWithScope($this->getFixturePath('ClassWithExtends.php'));

        $this->assertTrue($this->inherits($node, ParentConcern::class));
    }

    #[Test]
    public function it_detects_a_class_indirectly_uses_a_trait_via_another_trait(): void
    {
        $node = $this->getClassNodeWithScope($this->getFixturePath('ClassWithChildTrait.php'));

        $this->assertTrue($this->inherits($node, ParentConcern::class));
    }

    #[Test]
    public function it_detects_a_class_does_not_inherit_an_unrelated_class(): void
    {
        $node = $this->getClassNodeWithScope($this->getFixturePath('ClassWithTrait.php'));

        $this->assertTrue($this->doesNotInherit($node, ParentClass::class));
    }

    #[Test]
    public function it_accepts_an_array_of_expected_values(): void
    {
        $node = $this->getClassNode($this->getFixturePath('ClassWithTraitAndInterface.php'));

        $this->assertTrue($this->inherits($node, [Contract::class, Concern::class]));
    }

    #[Test]
    public function it_returns_false_when_none_of_the_expected_values_match(): void
    {
        $node = $this->getClassNode($this->getFixturePath('ClassWithTrait.php'));

        $this->assertFalse($this->inherits($node, [Contract::class, ParentContract::class]));
    }

    #[Test]
    public function it_detects_an_enum_directly_implements_an_interface(): void
    {
        $node = $this->getEnumNode($this->getFixturePath('EnumWithInterface.php'));

        $this->assertTrue($this->inherits($node, Contract::class));
    }

    #[Test]
    public function it_detects_an_enum_does_not_directly_implement_an_unrelated_interface(): void
    {
        $node = $this->getEnumNode($this->getFixturePath('EnumWithTrait.php'));

        $this->assertFalse($this->inherits($node, Contract::class));
    }

    #[Test]
    public function it_detects_an_enum_directly_uses_a_trait(): void
    {
        $node = $this->getEnumNode($this->getFixturePath('EnumWithTrait.php'));

        $this->assertTrue($this->inherits($node, Concern::class));
    }

    #[Test]
    public function it_detects_an_enum_does_not_directly_use_an_unrelated_trait(): void
    {
        $node = $this->getEnumNode($this->getFixturePath('EnumWithInterface.php'));

        $this->assertFalse($this->inherits($node, Concern::class));
    }

    #[Test]
    public function it_detects_an_enum_indirectly_implements_a_parent_interface(): void
    {
        $node = $this->getEnumNodeWithScope($this->getFixturePath('EnumWithChildInterface.php'));

        $this->assertTrue($this->inherits($node, ParentContract::class));
    }

    #[Test]
    public function it_detects_an_enum_indirectly_uses_a_trait_via_another_trait(): void
    {
        $node = $this->getEnumNodeWithScope($this->getFixturePath('EnumWithChildTrait.php'));

        $this->assertTrue($this->inherits($node, ParentConcern::class));
    }

    #[Test]
    public function it_detects_an_enum_does_not_inherit_an_unrelated_class(): void
    {
        $node = $this->getEnumNodeWithScope($this->getFixturePath('EnumWithTrait.php'));

        $this->assertTrue($this->doesNotInherit($node, ParentClass::class));
    }

    #[Test]
    public function it_accepts_an_array_of_expected_values_for_an_enum(): void
    {
        $node = $this->getEnumNode($this->getFixturePath('EnumWithTraitAndInterface.php'));

        $this->assertTrue($this->inherits($node, [Contract::class, Concern::class]));
    }

    #[Test]
    public function it_returns_false_when_none_of_the_expected_values_match_for_an_enum(): void
    {
        $node = $this->getEnumNode($this->getFixturePath('EnumWithTrait.php'));

        $this->assertFalse($this->inherits($node, [Contract::class, ParentContract::class]));
    }

    #[Test]
    public function it_detects_an_interface_directly_extends_another_interface(): void
    {
        $node = $this->getInterfaceNode($this->getFixturePath('ChildContract.php'));

        $this->assertTrue($this->inherits($node, ParentContract::class));
    }

    #[Test]
    public function it_detects_an_interface_does_not_directly_extend_an_unrelated_interface(): void
    {
        $node = $this->getInterfaceNode($this->getFixturePath('Contract.php'));

        $this->assertFalse($this->inherits($node, ParentContract::class));
    }

    #[Test]
    public function it_detects_an_interface_does_not_inherit_an_unrelated_interface(): void
    {
        $node = $this->getInterfaceNode($this->getFixturePath('Contract.php'));

        $this->assertTrue($this->doesNotInherit($node, ParentContract::class));
    }

    #[Test]
    public function it_detects_a_trait_directly_uses_another_trait(): void
    {
        $node = $this->getTraitNode($this->getFixturePath('TraitWithTrait.php'));

        $this->assertTrue($this->inherits($node, Concern::class));
    }

    #[Test]
    public function it_detects_a_trait_does_not_directly_use_an_unrelated_trait(): void
    {
        $node = $this->getTraitNode($this->getFixturePath('TraitWithoutTrait.php'));

        $this->assertFalse($this->inherits($node, Concern::class));
    }

    #[Test]
    public function it_detects_a_trait_does_not_inherit_an_unrelated_trait(): void
    {
        $node = $this->getTraitNode($this->getFixturePath('TraitWithoutTrait.php'));

        $this->assertTrue($this->doesNotInherit($node, Concern::class));
    }
}
