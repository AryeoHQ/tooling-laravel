<?php

declare(strict_types=1);

namespace Tooling\PHPStan\Rules\PHPUnit;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\IdentifierRuleError;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\ShouldNotHappenException;

/**
 * @implements Rule<Class_>
 */
final class TestClassMustExtendTestCase implements Rule
{
    /** @var class-string */
    private string $testCaseClass;

    public function __construct(string $testCaseClass = 'Tests\\TestCase')
    {
        $this->testCaseClass = $testCaseClass;
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
        return $this->passes($node) ? [] : $this->buildError($node);
    }

    private function passes(Class_ $node): bool
    {
        return ! $this->violated($node);
    }

    private function violated(Class_ $node): bool
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

        return $this->doesNotExtendTestCase($node);
    }

    private function extendsTestCase(Class_ $node): bool
    {
        return $node->extends !== null && $node->extends->toString() === $this->testCaseClass;
    }

    private function doesNotExtendTestCase(Class_ $node): bool
    {
        return ! $this->extendsTestCase($node);
    }

    /**
     * @return array<array-key, IdentifierRuleError>
     */
    private function buildError(Class_ $node): array
    {
        return [
            RuleErrorBuilder::message('Test class must extend `Tests\\TestCase`.')
                ->identifier('phpunit.testClassMustExtendTestCase')
                ->build(),
        ];
    }
}
