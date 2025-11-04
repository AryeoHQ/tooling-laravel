<?php

declare(strict_types=1);

namespace Tooling\PHPStan\Rules\PHPUnit;

use PhpParser\Node;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Rules\IdentifierRuleError;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\ShouldNotHappenException;

/**
 * @implements Rule<ClassMethod>
 */
final class TestMethodMustHaveTestAttribute implements Rule
{
    private readonly ReflectionProvider $reflectionProvider;

    private string $testCaseClass;

    public function __construct(ReflectionProvider $reflectionProvider, string $testCaseClass = 'Tests\\TestCase')
    {
        $this->reflectionProvider = $reflectionProvider;
        $this->testCaseClass = $testCaseClass;
    }

    /**
     * {@inheritDoc}
     */
    public function getNodeType(): string
    {
        return ClassMethod::class;
    }

    /**
     * {@inheritDoc}
     *
     * @param  ClassMethod  $node
     *
     * @throws ShouldNotHappenException
     */
    public function processNode(Node $node, Scope $scope): array
    {
        return $this->passes($node, $scope) ? [] : $this->buildError($node);
    }

    private function passes(ClassMethod $node, Scope $scope): bool
    {
        return ! $this->violated($node, $scope);
    }

    private function violated(ClassMethod $node, Scope $scope): bool
    {
        // Ensure that the scope is a class.
        if (! $scope->isInClass()) {
            return false;
        }

        $scopeReflection = $scope->getClassReflection();

        // Ensure that the class is concrete.
        if ($scopeReflection->isAbstract()) {
            return false;
        }

        // Ensure that the method's class extends the `TestCase` class.
        if (! $this->reflectionProvider->hasClass($this->testCaseClass)) {
            return false;
        }

        if (! $scopeReflection->isSubclassOfClass(
            class: $this->reflectionProvider->getClass($this->testCaseClass)
        )) {
            return false;
        }

        // Ensure that the method is public.
        if (! $node->isPublic()) {
            return false;
        }

        // Skip magic methods and setUp/tearDown methods.
        $methodName = $node->name->toString();
        if (str_starts_with($methodName, '__') ||
            in_array($methodName, ['setUp', 'tearDown', 'setUpBeforeClass', 'tearDownAfterClass'], true)
        ) {
            return false;
        }

        // Check if the method has the #[Test] attribute.
        foreach ($node->attrGroups as $attrGroup) {
            foreach ($attrGroup->attrs as $attr) {
                if ($attr->name->toString() === 'Test' || $attr->name->toString() === 'PHPUnit\\Framework\\Attributes\\Test') {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * @param  ClassMethod  $node
     * @return array<array-key, IdentifierRuleError>
     */
    private function buildError(Node $node): array
    {
        return [
            RuleErrorBuilder::message('Test method must use the #[Test] attribute.')
                ->identifier('phpunit.testMethodMustHaveTestAttribute')
                ->line($node->name->getStartLine())
                ->build(),
        ];
    }
}
