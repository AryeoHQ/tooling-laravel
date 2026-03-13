<?php

declare(strict_types=1);

namespace Tooling\PhpStan\Rules\GeneratorCommands;

use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use PHPUnit\Framework\Attributes\Test;
use Tests\Tooling\Concerns\GetsFixtures;

/**
 * @extends RuleTestCase<ReferenceTestCasesMustImplementTestsReference>
 */
class ReferenceTestCasesMustImplementTestsReferenceTest extends RuleTestCase
{
    use GetsFixtures;

    protected function getRule(): Rule
    {
        return new ReferenceTestCasesMustImplementTestsReference;
    }

    #[Test]
    public function it_passes_when_reference_test_cases_implements_tests_reference(): void
    {
        $this->analyse([$this->getFixturePath('PhpStan/GeneratorCommands/ValidReferenceTestCasesWithTestsReference.php')], []);
    }

    #[Test]
    public function it_passes_when_class_does_not_use_reference_test_cases(): void
    {
        $this->analyse([$this->getFixturePath('PhpStan/GeneratorCommands/ValidNoGeneratesFile.php')], []);
    }

    #[Test]
    public function it_fails_when_reference_test_cases_does_not_implement_tests_reference(): void
    {
        $this->analyse([$this->getFixturePath('PhpStan/GeneratorCommands/InvalidReferenceTestCasesWithoutTestsReference.php')], [
            [
                'ReferenceTestCases must implement TestsReference.',
                9,
            ],
        ]);
    }
}
