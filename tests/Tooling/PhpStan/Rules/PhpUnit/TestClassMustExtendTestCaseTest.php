<?php

declare(strict_types=1);

namespace Tests\Tooling\PhpStan\Rules\PhpUnit;

use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use PHPUnit\Framework\Attributes\Test;
use Tests\Tooling\Concerns\GetsFixtures;
use Tooling\PhpStan\Rules\PhpUnit\TestClassMustExtendTestCase;

/**
 * @extends RuleTestCase<TestClassMustExtendTestCase>
 */
class TestClassMustExtendTestCaseTest extends RuleTestCase
{
    use GetsFixtures;

    protected function getRule(): Rule
    {
        return new TestClassMustExtendTestCase;
    }

    #[Test]
    public function it_passes_when_test_class_extends_test_case(): void
    {
        $this->analyse([$this->getFixturePath('PhpStan/PhpUnit/ValidTestClassTest.php')], []);
    }

    #[Test]
    public function it_fails_when_test_class_does_not_extend_test_case(): void
    {
        $this->analyse([$this->getFixturePath('PhpStan/PhpUnit/InvalidTestClassTest.php')], [
            [
                'Test class must extend: Tests\\TestCase.',
                7,
            ],
        ]);
    }
}
