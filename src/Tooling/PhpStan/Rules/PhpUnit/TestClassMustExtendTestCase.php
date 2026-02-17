<?php

declare(strict_types=1);

namespace Tooling\PhpStan\Rules\PhpUnit;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PHPStan\Analyser\Scope;
use Tooling\PhpStan\Rules\Rule;
use Tooling\Rules\Attributes\NodeType;

/**
 * @extends Rule<Class_>
 */
#[NodeType(Class_::class)]
final class TestClassMustExtendTestCase extends Rule
{
    /** @var Collection<int, class-string> */
    private Collection $testCaseClasses;

    /**
     * @param  class-string|array<array-key, class-string>  $testCaseClass
     */
    public function __construct(string|array $testCaseClass = 'Tests\\TestCase')
    {
        $this->testCaseClasses = collect(Arr::wrap($testCaseClass));
    }

    /**
     * @param  Class_  $node
     */
    public function shouldHandle(Node $node, Scope $scope): bool
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

        return $this->doesNotInherit(
            $node, $this->testCaseClasses->all()
        );
    }

    public function handle(Node $node, Scope $scope): void
    {
        $classes = $this->testCaseClasses->join(', ', ', or ');

        $this->error(
            message: "Test class must extend: {$classes}.",
            line: $node->getStartLine(),
            identifier: 'phpunit.testClassMustExtendTestCase'
        );
    }
}
