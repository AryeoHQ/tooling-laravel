<?php

declare(strict_types=1);

namespace Tests\Tooling\Rector\Rules;

use PhpParser\Node\Stmt\Class_;
use PHPUnit\Framework\Attributes\Test;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use Tests\Fixtures\Tooling\Contract;
use Tests\Fixtures\Tooling\ParentClass;
use Tests\TestCase;
use Tests\Tooling\Concerns\GetsFixtures;
use Tooling\Rector\Rules\AddInterfaceByClass;
use Tooling\Rector\Rules\Provides\ValidatesInheritance;
use Tooling\Rector\Testing\ParsesNodes;
use Tooling\Rector\Testing\ResolvesRectorRules;

class AddInterfaceByClassTest extends TestCase
{
    use GetsFixtures;
    use ParsesNodes;
    use ResolvesRectorRules;
    use ValidatesInheritance;

    #[Test]
    public function it_has_rule_definition(): void
    {
        $rule = $this->resolveRule(AddInterfaceByClass::class);

        $ruleDefinition = $rule->getRuleDefinition();

        $this->assertInstanceOf(RuleDefinition::class, $ruleDefinition);
        $this->assertSame('Add interface by parent class', $ruleDefinition->getDescription());
    }

    #[Test]
    public function it_adds_interface_when_class_extends_parent(): void
    {
        $classNode = $this->getClassNode($this->getFixturePath('ClassWithExtends.php'));

        $this->assertFalse($this->inherits($classNode, Contract::class));

        $rule = $this->resolveRule(AddInterfaceByClass::class);
        $rule->configure([ParentClass::class => Contract::class]);

        $result = $rule->refactor($classNode);

        $this->assertInstanceOf(Class_::class, $result);
        $this->assertTrue($this->inherits($result, Contract::class));
    }

    #[Test]
    public function it_does_not_add_interface_when_already_implemented(): void
    {
        $classNode = $this->getClassNode($this->getFixturePath('ClassWithExtendsAndInterface.php'));

        $this->assertTrue($this->inherits($classNode, Contract::class));

        $rule = $this->resolveRule(AddInterfaceByClass::class);
        $rule->configure([ParentClass::class => Contract::class]);

        $result = $rule->refactor($classNode);

        $this->assertNull($result);
    }

    #[Test]
    public function it_returns_null_when_class_does_not_extend_parent(): void
    {
        $classNode = $this->getClassNode($this->getFixturePath('ClassWithInterface.php'));

        $rule = $this->resolveRule(AddInterfaceByClass::class);
        $rule->configure([ParentClass::class => Contract::class]);

        $result = $rule->refactor($classNode);

        $this->assertNull($result);
    }
}
