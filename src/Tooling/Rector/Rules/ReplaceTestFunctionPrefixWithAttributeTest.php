<?php

declare(strict_types=1);

namespace Tooling\Rector\Rules;

use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PHPUnit\Framework\Attributes\Test;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use Tests\TestCase;
use Tests\Tooling\Concerns\GetsFixtures;
use Tooling\Rector\Rules\Provides\ValidatesAttributes;
use Tooling\Rector\Testing\ParsesNodes;
use Tooling\Rector\Testing\ResolvesRectorRules;

class ReplaceTestFunctionPrefixWithAttributeTest extends TestCase
{
    use GetsFixtures;
    use ParsesNodes;
    use ResolvesRectorRules;
    use ValidatesAttributes;

    #[Test]
    public function it_has_rule_definition(): void
    {
        $rule = $this->resolveRule(ReplaceTestFunctionPrefixWithAttribute::class);

        $ruleDefinition = $rule->getRuleDefinition();

        $this->assertInstanceOf(RuleDefinition::class, $ruleDefinition);
        $this->assertSame('Replace test-prefixed methods with the #[Test] attribute', $ruleDefinition->getDescription());
    }

    #[Test]
    public function it_replaces_test_prefixed_methods_with_attribute(): void
    {
        $classNode = $this->getClassNode($this->getFixturePath('TestCaseWithTestPrefixedMethods.php'));

        $rule = $this->resolveRule(ReplaceTestFunctionPrefixWithAttribute::class);
        $result = $rule->refactor($classNode);

        $this->assertInstanceOf(Class_::class, $result);

        $camelCase = $result->getMethod('camelCase');
        $this->assertInstanceOf(ClassMethod::class, $camelCase);
        $this->assertTrue($this->hasAttribute($camelCase, Test::class));

        $snakeCase = $result->getMethod('snake_case');
        $this->assertInstanceOf(ClassMethod::class, $snakeCase);
        $this->assertTrue($this->hasAttribute($snakeCase, Test::class));
    }

    #[Test]
    public function it_leaves_non_test_methods_untouched(): void
    {
        $classNode = $this->getClassNode($this->getFixturePath('TestCaseWithTestPrefixedMethods.php'));

        $rule = $this->resolveRule(ReplaceTestFunctionPrefixWithAttribute::class);
        $result = $rule->refactor($classNode);

        $this->assertInstanceOf(Class_::class, $result);

        $helper = $result->getMethod('helper');
        $this->assertInstanceOf(ClassMethod::class, $helper);
        $this->assertFalse($this->hasAttribute($helper, Test::class));
    }

    #[Test]
    public function it_leaves_non_public_and_static_methods_untouched(): void
    {
        $classNode = $this->getClassNode($this->getFixturePath('TestCaseWithTestPrefixedMethods.php'));

        $rule = $this->resolveRule(ReplaceTestFunctionPrefixWithAttribute::class);
        $result = $rule->refactor($classNode);

        $this->assertInstanceOf(Class_::class, $result);

        $protected = $result->getMethod('testProtected');
        $this->assertInstanceOf(ClassMethod::class, $protected);
        $this->assertFalse($this->hasAttribute($protected, Test::class));

        $static = $result->getMethod('testStatic');
        $this->assertInstanceOf(ClassMethod::class, $static);
        $this->assertFalse($this->hasAttribute($static, Test::class));
    }

    #[Test]
    public function it_strips_the_prefix_without_duplicating_an_existing_attribute(): void
    {
        $classNode = $this->getClassNode($this->getFixturePath('TestCaseWithTestAttribute.php'));

        $rule = $this->resolveRule(ReplaceTestFunctionPrefixWithAttribute::class);
        $result = $rule->refactor($classNode);

        $this->assertInstanceOf(Class_::class, $result);

        $method = $result->getMethod('alreadyAttributed');
        $this->assertInstanceOf(ClassMethod::class, $method);
        $this->assertTrue($this->hasAttribute($method, Test::class));

        $testAttributes = collect($method->attrGroups)
            ->flatMap(fn ($attributeGroup) => $attributeGroup->attrs)
            ->filter(fn ($attribute) => $attribute->name->toString() === Test::class);

        $this->assertCount(1, $testAttributes);
    }

    #[Test]
    public function it_returns_null_for_non_test_classes(): void
    {
        $classNode = $this->getClassNode($this->getFixturePath('ClassWithTestPrefixedMethod.php'));

        $rule = $this->resolveRule(ReplaceTestFunctionPrefixWithAttribute::class);
        $result = $rule->refactor($classNode);

        $this->assertNull($result);
    }
}
