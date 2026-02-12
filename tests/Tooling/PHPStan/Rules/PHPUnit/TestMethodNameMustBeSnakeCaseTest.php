<?php

declare(strict_types=1);

namespace Tests\Tooling\PHPStan\Rules\PHPUnit;

use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use PHPUnit\Framework\Attributes\Test;
use Tests\Tooling\Concerns\GetsFixtures;
use Tooling\PHPStan\Rules\PHPUnit\TestMethodNameMustBeSnakeCase;

/**
 * @extends RuleTestCase<TestMethodNameMustBeSnakeCase>
 */
class TestMethodNameMustBeSnakeCaseTest extends RuleTestCase
{
    use GetsFixtures;

    protected function getRule(): Rule
    {
        return new TestMethodNameMustBeSnakeCase(
            self::getContainer()->getByType(\PHPStan\Reflection\ReflectionProvider::class)
        );
    }

    #[Test]
    public function it_passes_when_test_method_is_snake_case(): void
    {
        $this->analyse([$this->getFixturePath('PHPStan/PHPUnit/ValidSnakeCaseMethod.php')], []);
    }

    #[Test]
    public function it_fails_when_test_method_is_not_snake_case(): void
    {
        $this->analyse([$this->getFixturePath('PHPStan/PHPUnit/InvalidCamelCaseMethod.php')], [
            [
                'Test method must be snake cased.',
                9,
            ],
        ]);
    }
}
