<?php

declare(strict_types=1);

namespace Tests\Tooling\PHPStan\Rules\PHPUnit;

use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use PHPUnit\Framework\Attributes\Test;
use Tests\Tooling\Concerns\GetsFixtures;
use Tooling\PHPStan\Rules\PHPUnit\TestMethodMustHaveTestAttribute;

/**
 * @extends RuleTestCase<TestMethodMustHaveTestAttribute>
 */
class TestMethodMustHaveTestAttributeTest extends RuleTestCase
{
    use GetsFixtures;

    protected function getRule(): Rule
    {
        return new TestMethodMustHaveTestAttribute(
            self::getContainer()->getByType(\PHPStan\Reflection\ReflectionProvider::class)
        );
    }

    #[Test]
    public function it_passes_when_test_method_has_test_attribute(): void
    {
        $this->analyse([$this->getFixturePath('PHPStan/PHPUnit/ValidTestMethod.php')], []);
    }

    #[Test]
    public function it_fails_when_test_method_does_not_have_test_attribute(): void
    {
        $this->analyse([$this->getFixturePath('PHPStan/PHPUnit/InvalidTestMethod.php')], [
            [
                'Test method must use the #[Test] attribute.',
                9,
            ],
        ]);
    }
}
