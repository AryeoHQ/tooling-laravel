<?php

declare(strict_types=1);

namespace Tests\Tooling\Rector\Rules\Provides;

use PHPUnit\Framework\Attributes\Test;
use Tests\Fixtures\Tooling\Contract;
use Tests\Fixtures\Tooling\ParentContract;

trait ManagesInterfacesCases
{
    #[Test]
    public function it_adds_an_interface_to_a_class(): void
    {
        $node = $this->getClassNode($this->getFixturePath('ClassWithTrait.php'));

        $this->assertFalse($this->inherits($node, Contract::class));

        $node = $this->addInterface($node, Contract::class);

        $this->assertTrue($this->inherits($node, Contract::class));
    }

    #[Test]
    public function it_does_not_add_an_interface_that_is_already_implemented_on_a_class(): void
    {
        $node = $this->getClassNode($this->getFixturePath('ClassWithInterface.php'));

        $implementsCount = count($node->implements);

        $node = $this->addInterface($node, Contract::class);

        $this->assertCount($implementsCount, $node->implements);
    }

    #[Test]
    public function it_removes_an_interface_from_a_class(): void
    {
        $node = $this->getClassNode($this->getFixturePath('ClassWithInterface.php'));

        $this->assertTrue($this->inherits($node, Contract::class));

        $node = $this->removeInterface($node, Contract::class);

        $this->assertFalse($this->inherits($node, Contract::class));
    }

    #[Test]
    public function it_does_not_modify_a_class_when_removing_an_interface_it_does_not_implement(): void
    {
        $node = $this->getClassNode($this->getFixturePath('ClassWithTrait.php'));

        $implementsCount = count($node->implements);

        $node = $this->removeInterface($node, Contract::class);

        $this->assertCount($implementsCount, $node->implements);
    }

    #[Test]
    public function it_adds_an_interface_to_an_enum(): void
    {
        $node = $this->getEnumNode($this->getFixturePath('EnumWithTrait.php'));

        $this->assertFalse($this->inherits($node, Contract::class));

        $node = $this->addInterface($node, Contract::class);

        $this->assertTrue($this->inherits($node, Contract::class));
    }

    #[Test]
    public function it_does_not_add_an_interface_that_is_already_implemented_on_an_enum(): void
    {
        $node = $this->getEnumNode($this->getFixturePath('EnumWithInterface.php'));

        $implementsCount = count($node->implements);

        $node = $this->addInterface($node, Contract::class);

        $this->assertCount($implementsCount, $node->implements);
    }

    #[Test]
    public function it_removes_an_interface_from_an_enum(): void
    {
        $node = $this->getEnumNode($this->getFixturePath('EnumWithInterface.php'));

        $this->assertTrue($this->inherits($node, Contract::class));

        $node = $this->removeInterface($node, Contract::class);

        $this->assertFalse($this->inherits($node, Contract::class));
    }

    #[Test]
    public function it_adds_an_interface_to_an_interface(): void
    {
        $node = $this->getInterfaceNode($this->getFixturePath('Contract.php'));

        $this->assertEmpty($node->extends);

        $node = $this->addInterface($node, ParentContract::class);

        $this->assertCount(1, $node->extends);
        $this->assertSame(ParentContract::class, $node->extends[0]->toString());
    }

    #[Test]
    public function it_does_not_add_an_interface_that_is_already_extended_on_an_interface(): void
    {
        $node = $this->getInterfaceNode($this->getFixturePath('ChildContract.php'));

        $extendsCount = count($node->extends);

        $node = $this->addInterface($node, ParentContract::class);

        $this->assertCount($extendsCount, $node->extends);
    }

    #[Test]
    public function it_removes_an_interface_from_an_interface(): void
    {
        $node = $this->getInterfaceNode($this->getFixturePath('ChildContract.php'));

        $this->assertCount(1, $node->extends);

        $node = $this->removeInterface($node, ParentContract::class);

        $this->assertEmpty($node->extends);
    }

    #[Test]
    public function it_does_not_modify_an_enum_when_removing_an_interface_it_does_not_implement(): void
    {
        $node = $this->getEnumNode($this->getFixturePath('EnumWithTrait.php'));

        $implementsCount = count($node->implements);

        $node = $this->removeInterface($node, Contract::class);

        $this->assertCount($implementsCount, $node->implements);
    }

    #[Test]
    public function it_does_not_modify_an_interface_when_removing_an_interface_it_does_not_extend(): void
    {
        $node = $this->getInterfaceNode($this->getFixturePath('Contract.php'));

        $extendsCount = count($node->extends);

        $node = $this->removeInterface($node, ParentContract::class);

        $this->assertCount($extendsCount, $node->extends);
    }
}
