<?php

declare(strict_types=1);

namespace Tests\Tooling\Rector\Rules;

use PhpParser\Node\Stmt\Class_;
use PHPUnit\Framework\Attributes\Test;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use Tests\TestCase;
use Tests\Tooling\Concerns\GetsFixtures;
use Tests\Fixtures\Tooling\Concern;
use Tests\Fixtures\Tooling\Contract;
use Tooling\Rector\Rules\AddTraitByInterface;
use Tooling\Rector\Rules\Provides\ParsesNodes;
use Tooling\Rector\Rules\Provides\ValidatesInheritance;

class AddTraitByInterfaceTest extends TestCase
{
    use GetsFixtures;
    use ParsesNodes;
    use ValidatesInheritance;

    #[Test]
    public function it_has_rule_definition(): void
    {
        $rule = app(AddTraitByInterface::class);

        $ruleDefinition = $rule->getRuleDefinition();

        $this->assertInstanceOf(RuleDefinition::class, $ruleDefinition);
        $this->assertSame('Add trait by implemented interface', $ruleDefinition->getDescription());
    }

    #[Test]
    public function it_adds_trait_when_class_implements_interface(): void
    {
        $classNode = $this->getClassNode($this->getFixturePath('ClassWithInterface.php'));

        $this->assertFalse($this->inherits($classNode, Concern::class));

        $rule = app(AddTraitByInterface::class);
        $rule->configure([Contract::class => Concern::class]);

        $result = $rule->refactor($classNode);

        $this->assertInstanceOf(Class_::class, $result);
        $this->assertTrue($this->inherits($result, Concern::class));
    }

    #[Test]
    public function it_does_not_add_trait_when_already_used(): void
    {
        $classNode = $this->getClassNode($this->getFixturePath('ClassWithTraitAndInterface.php'));

        $this->assertTrue($this->inherits($classNode, Concern::class));

        $rule = app(AddTraitByInterface::class);
        $rule->configure([Contract::class => Concern::class]);

        $result = $rule->refactor($classNode);

        $this->assertNull($result);
    }

    #[Test]
    public function it_returns_null_when_interface_is_not_implemented(): void
    {
        $classNode = $this->getClassNode($this->getFixturePath('ClassWithTrait.php'));

        $rule = app(AddTraitByInterface::class);
        $rule->configure([Contract::class => Concern::class]);

        $result = $rule->refactor($classNode);

        $this->assertNull($result);
    }
}
