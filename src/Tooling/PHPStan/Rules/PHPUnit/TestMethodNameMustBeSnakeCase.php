<?php

declare(strict_types=1);

namespace Tooling\PHPStan\Rules\PHPUnit;

use PhpParser\Node;
use PHPStan\Rules\Rule;
use Illuminate\Support\Arr;
use PHPStan\Analyser\Scope;
use Illuminate\Support\Collection;
use PHPStan\Rules\RuleErrorBuilder;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\ShouldNotHappenException;
use PHPStan\Rules\IdentifierRuleError;
use PHPStan\Reflection\ReflectionProvider;

/**
 * @implements Rule<ClassMethod>
 */
final class TestMethodNameMustBeSnakeCase implements Rule
{
    private readonly ReflectionProvider $reflectionProvider;

    private Collection $testCaseClasses;

    public function __construct(ReflectionProvider $reflectionProvider, string|array $testCaseClass = 'Tests\\TestCase')
    {
        $this->testCaseClasses = collect(Arr::wrap($testCaseClass));
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

        // Ensure that the method's class extends the allowed base TestCase class.
        $subClassOf = $this->testCaseClasses->filter(
            fn (string $testCaseClass) => $scopeReflection->isSubclassOfClass(
                class: $this->reflectionProvider->getClass($testCaseClass)
            )
        );

        if ($subClassOf->isEmpty()) {
            return false;
        }

        // Ensure that the method is public.
        if (! $node->isPublic()) {
            return false;
        }

        return $this->isNotSnakeCased($node);
    }

    private function isSnakeCased(ClassMethod $node): bool
    {
        return preg_match('/^[a-z][a-z0-9_]*$/', $node->name->toString()) === 1;
    }

    private function isNotSnakeCased(ClassMethod $node): bool
    {
        return ! $this->isSnakeCased($node);
    }

    /**
     * @return array<array-key, IdentifierRuleError>
     */
    private function buildError(ClassMethod $node): array
    {
        return [
            RuleErrorBuilder::message('Test method must be snake cased.')
                ->identifier('phpunit.testMethodNameMustBeSnakeCase')
                ->line($node->name->getStartLine())
                ->build(),
        ];
    }
}
