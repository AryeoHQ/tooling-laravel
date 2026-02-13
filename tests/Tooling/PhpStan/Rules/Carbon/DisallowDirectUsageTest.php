<?php

declare(strict_types=1);

namespace Tests\Tooling\PhpStan\Rules\Carbon;

use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use PHPUnit\Framework\Attributes\Test;
use Tests\Tooling\Concerns\GetsFixtures;
use Tooling\PhpStan\Rules\Carbon\DisallowDirectUsage;

/**
 * @extends RuleTestCase<DisallowDirectUsage>
 */
class DisallowDirectUsageTest extends RuleTestCase
{
    use GetsFixtures;

    protected function getRule(): Rule
    {
        return new DisallowDirectUsage;
    }

    #[Test]
    public function it_passes_when_using_date_facade(): void
    {
        $this->analyse([$this->getFixturePath('PhpStan/Carbon/ValidCarbonUsage.php')], []);
    }

    #[Test]
    public function it_fails_when_using_carbon_directly(): void
    {
        $this->analyse([$this->getFixturePath('PhpStan/Carbon/InvalidCarbonUsage.php')], [
            [
                'Direct use of Carbon is disallowed; use the `Date` facade instead, e.g. `Date::now()`.',
                13,
            ],
            [
                'Direct use of Carbon is disallowed; use the `Date` facade instead, e.g. `Date::now()`.',
                14,
            ],
        ]);
    }
}
