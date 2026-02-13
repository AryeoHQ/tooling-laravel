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
final class TestMethodMustNotHaveTestPrefix extends Rule
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

        return str_starts_with($node->name->toString(), 'test');
    }

    /**
     * @param  ClassMethod  $node
     */
    public function handle(Node $node, Scope $scope): void
    {
        $this->error(
            message: 'Test method must not use `test` prefix.',
            line: $node->name->getStartLine(),
            identifier: 'phpunit.testMethodMustNotHaveTestPrefix'
        );
    }
}
