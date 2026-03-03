<?php

declare(strict_types=1);

namespace Tests\Tooling\PhpStan\Rules;

use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use PHPUnit\Framework\Attributes\Test;
use Tests\Tooling\Concerns\GetsFixtures;
use Tooling\PhpStan\Rules\TestsReferenceMustHaveReferenceTestCases;

/**
 * @extends RuleTestCase<TestsReferenceMustHaveReferenceTestCases>
 */
class TestsReferenceMustHaveReferenceTestCasesTest extends RuleTestCase
{
    use GetsFixtures;

    protected function getRule(): Rule
    {
        return new TestsReferenceMustHaveReferenceTestCases;
    }

    #[Test]
    public function it_passes_when_tests_reference_uses_reference_test_cases(): void
    {
        $this->analyse([$this->getFixturePath('PhpStan/TestsReference/ValidWithReferenceTestCases.php')], []);
    }

    #[Test]
    public function it_passes_when_class_does_not_implement_tests_reference(): void
    {
        $this->analyse([$this->getFixturePath('PhpStan/TestsReference/ValidWithoutTestsReference.php')], []);
    }

    #[Test]
    public function it_fails_when_tests_reference_does_not_use_reference_test_cases(): void
    {
        $this->analyse([$this->getFixturePath('PhpStan/TestsReference/InvalidWithoutReferenceTestCases.php')], [
            [
                'TestsReference must use ReferenceTestCases.',
                10,
            ],
        ]);
    }
}
