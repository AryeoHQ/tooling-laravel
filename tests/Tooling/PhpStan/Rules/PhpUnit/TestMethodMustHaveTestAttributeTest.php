<?php

declare(strict_types=1);

namespace Tests\Tooling\PhpStan\Rules\PhpUnit;

use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use PHPUnit\Framework\Attributes\Test;
use Tests\Tooling\Concerns\GetsFixtures;
use Tooling\PhpStan\Rules\PhpUnit\TestMethodMustHaveTestAttribute;

/**
 * @extends RuleTestCase<TestMethodMustHaveTestAttribute>
 */
class TestMethodMustHaveTestAttributeTest extends RuleTestCase
{
    use GetsFixtures;

    protected function getRule(): Rule
    {
        return new TestMethodMustHaveTestAttribute;
    }

    #[Test]
    public function it_passes_when_test_method_has_test_attribute(): void
    {
        $this->analyse([$this->getFixturePath('PhpStan/PhpUnit/ValidTestMethodTest.php')], []);
    }

    #[Test]
    public function it_fails_when_test_method_does_not_have_test_attribute(): void
    {
        $this->analyse([$this->getFixturePath('PhpStan/PhpUnit/InvalidTestMethodTest.php')], [
            [
                'Test method must use the #[Test] attribute.',
                9,
            ],
        ]);
    }
}
