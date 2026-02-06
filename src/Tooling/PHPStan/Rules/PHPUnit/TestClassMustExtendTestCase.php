<?php

declare(strict_types=1);

namespace Tooling\PHPStan\Rules\PHPUnit;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Rules\IdentifierRuleError;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\ShouldNotHappenException;

/**
 * @implements Rule<Class_>
 */
final class TestClassMustExtendTestCase implements Rule
{
    private readonly ReflectionProvider $reflectionProvider;

    /** @var Collection<int, class-string> */
    private Collection $testCaseClasses;

    /**
     * @param  class-string|array<array-key, class-string>  $testCaseClass
     */
    public function __construct(ReflectionProvider $reflectionProvider, string|array $testCaseClass = 'Tests\\TestCase')
    {
        $this->reflectionProvider = $reflectionProvider;
        $this->testCaseClasses = collect(Arr::wrap($testCaseClass));
    }

    /**
     * {@inheritDoc}
     */
    public function getNodeType(): string
    {
        return Class_::class;
    }

    /**
     * {@inheritDoc}
     *
     * @param  Class_  $node
     *
     * @throws ShouldNotHappenException
     */
    public function processNode(Node $node, Scope $scope): array
    {
        return $this->passes($node, $scope) ? [] : $this->buildError($node);
    }

    private function passes(Class_ $node, Scope $scope): bool
    {
        return ! $this->violated($node, $scope);
    }

    private function violated(Class_ $node, Scope $scope): bool
    {
        if ($node->isAbstract()) {
            return false;
        }

        if ($node->isAnonymous()) {
            return false;
        }

        if ($node->name === null) {
            return false;
        }

        if (! str_ends_with($node->name->toString(), 'Test')) {
            return false;
        }

        return $this->doesNotExtendTestCase($node, $scope);
    }

    private function extendsTestCase(Class_ $node, Scope $scope, string $testCaseClass): bool
    {
        $className = $node->namespacedName->toString();

        if (! $this->reflectionProvider->hasClass($className)) {
            return false;
        }

        $classReflection = $this->reflectionProvider->getClass($className);

        if (! $this->reflectionProvider->hasClass($testCaseClass)) {
            return false;
        }

        return $classReflection->isSubclassOfClass($this->reflectionProvider->getClass($testCaseClass));
    }

    private function doesNotExtendTestCase(Class_ $node, Scope $scope): bool
    {
        return $this->testCaseClasses->filter(
            fn (string $testCaseClass) => $this->extendsTestCase($node, $scope, $testCaseClass)
        )->isEmpty();
    }

    /**
     * @return array<array-key, IdentifierRuleError>
     */
    private function buildError(Class_ $node): array
    {
        $classes = $this->testCaseClasses->join(', ', ' or ');

        return [
            RuleErrorBuilder::message("Test class must extend: {$classes}.")
                ->identifier('phpunit.testClassMustExtendTestCase')
                ->build(),
        ];
    }
}
