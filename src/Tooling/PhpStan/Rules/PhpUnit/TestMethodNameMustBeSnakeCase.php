<?php

declare(strict_types=1);

namespace Tooling\PhpStan\Rules\PhpUnit;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use PhpParser\Node;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ReflectionProvider;
use Tooling\PhpStan\Rules\Rule;
use Tooling\Rules\Attributes\NodeType;

/**
 * @extends Rule<ClassMethod>
 */
#[NodeType(ClassMethod::class)]
final class TestMethodNameMustBeSnakeCase extends Rule
{
    private readonly ReflectionProvider $reflectionProvider;

    /** @var Collection<int, class-string> */
    private Collection $testCaseClasses;

    /**
     * @param  class-string|array<array-key, class-string>  $testCaseClass
     */
    public function __construct(ReflectionProvider $reflectionProvider, string|array $testCaseClass = 'Tests\\TestCase')
    {
        $this->testCaseClasses = collect(Arr::wrap($testCaseClass));
        $this->reflectionProvider = $reflectionProvider;
    }

    /**
     * @param  ClassMethod  $node
     */
    public function shouldHandle(Node $node, Scope $scope): bool
    {
        if (! $scope->isInClass()) {
            return false;
        }

        $scopeReflection = $scope->getClassReflection();

        if ($scopeReflection->isAbstract()) {
            return false;
        }

        if (! $this->inherits($scopeReflection, $this->testCaseClasses->all(), $this->reflectionProvider)) {
            return false;
        }

        if (! $node->isPublic()) {
            return false;
        }

        return $this->isNotSnakeCased($node);
    }

    /**
     * @param  ClassMethod  $node
     */
    public function handle(Node $node, Scope $scope): void
    {
        $this->error(
            message: 'Test method must be snake cased.',
            line: $node->name->getStartLine(),
            identifier: 'phpunit.testMethodNameMustBeSnakeCase'
        );
    }

    private function isSnakeCased(ClassMethod $node): bool
    {
        return preg_match('/^[a-z][a-z0-9_]*$/', $node->name->toString()) === 1;
    }

    private function isNotSnakeCased(ClassMethod $node): bool
    {
        return ! $this->isSnakeCased($node);
    }
}
