<?php

declare(strict_types=1);

namespace Tests\Tooling\PHPStan\Rules\PHPUnit;

use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use PHPUnit\Framework\Attributes\Test;
use Tests\Tooling\Concerns\GetsFixtures;
use Tooling\PHPStan\Rules\PHPUnit\TestClassMustExtendTestCase;

/**
 * @extends RuleTestCase<TestClassMustExtendTestCase>
 */
class TestClassMustExtendTestCaseTest extends RuleTestCase
{
    use GetsFixtures;

    protected function getRule(): Rule
    {
        return new TestClassMustExtendTestCase(
            self::getContainer()->getByType(\PHPStan\Reflection\ReflectionProvider::class)
        );
    }

    #[Test]
    public function it_passes_when_test_class_extends_test_case(): void
    {
        $this->analyse([$this->getFixturePath('PHPStan/PHPUnit/ValidTestClass.php')], []);
    }

    #[Test]
    public function it_fails_when_test_class_does_not_extend_test_case(): void
    {
        $this->analyse([$this->getFixturePath('PHPStan/PHPUnit/InvalidTestClass.php')], [
            [
                'Test class must extend: Tests\\TestCase.',
                7,
            ],
        ]);
    }
}
