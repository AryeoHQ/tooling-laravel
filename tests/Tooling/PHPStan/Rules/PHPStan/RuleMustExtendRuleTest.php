<?php

declare(strict_types=1);

namespace Tests\Tooling\PHPStan\Rules\PHPStan;

use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use PHPUnit\Framework\Attributes\Test;
use Tests\Tooling\Concerns\GetsFixtures;
use Tooling\PHPStan\Rules\PHPStan\RuleMustExtendRule;

/**
 * @extends RuleTestCase<RuleMustExtendRule>
 */
class RuleMustExtendRuleTest extends RuleTestCase
{
    use GetsFixtures;

    protected function getRule(): Rule
    {
        return new RuleMustExtendRule(
            self::getContainer()->getByType(\PHPStan\Reflection\ReflectionProvider::class)
        );
    }

    #[Test]
    public function it_passes_when_rule_extends_base_rule(): void
    {
        $this->analyse([$this->getFixturePath('PHPStan/PHPStan/ValidRule.php')], []);
    }

    #[Test]
    public function it_fails_when_rule_directly_implements_interface(): void
    {
        $this->analyse([$this->getFixturePath('PHPStan/PHPStan/InvalidRule.php')], [
            [
                'PHPStan rule must extend Tooling\\PHPStan\\Rules\\Rule.',
                12,
            ],
        ]);
    }
}
