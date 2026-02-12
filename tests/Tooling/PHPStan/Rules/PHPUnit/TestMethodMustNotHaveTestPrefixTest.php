<?php

declare(strict_types=1);

namespace Tests\Tooling\PHPStan\Rules\PHPUnit;

use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use PHPUnit\Framework\Attributes\Test;
use Tests\Tooling\Concerns\GetsFixtures;
use Tooling\PHPStan\Rules\PHPUnit\TestMethodMustNotHaveTestPrefix;

/**
 * @extends RuleTestCase<TestMethodMustNotHaveTestPrefix>
 */
class TestMethodMustNotHaveTestPrefixTest extends RuleTestCase
{
    use GetsFixtures;

    protected function getRule(): Rule
    {
        return new TestMethodMustNotHaveTestPrefix(
            self::getContainer()->getByType(\PHPStan\Reflection\ReflectionProvider::class)
        );
    }

    #[Test]
    public function it_passes_when_test_method_does_not_have_test_prefix(): void
    {
        $this->analyse([$this->getFixturePath('PHPStan/PHPUnit/ValidTestMethodName.php')], []);
    }

    #[Test]
    public function it_fails_when_test_method_has_test_prefix(): void
    {
        $this->analyse([$this->getFixturePath('PHPStan/PHPUnit/InvalidTestMethodPrefix.php')], [
            [
                'Test method must not use `test` prefix.',
                9,
            ],
        ]);
    }
}
