<?php

declare(strict_types=1);

namespace Tests\Tooling\PhpStan\Rules\PhpStan;

use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use PHPUnit\Framework\Attributes\Test;
use Tests\Tooling\Concerns\GetsFixtures;
use Tooling\PhpStan\Rules\PhpStan\RuleMustExtendRule;

/**
 * @extends RuleTestCase<RuleMustExtendRule>
 */
class RuleMustExtendRuleTest extends RuleTestCase
{
    use GetsFixtures;

    protected function getRule(): Rule
    {
        return new RuleMustExtendRule;
    }

    #[Test]
    public function it_passes_when_rule_extends_base_rule(): void
    {
        $this->analyse([$this->getFixturePath('PhpStan/PhpStan/ValidRule.php')], []);
    }

    #[Test]
    public function it_fails_when_rule_directly_implements_interface(): void
    {
        $this->analyse([$this->getFixturePath('PhpStan/PhpStan/InvalidRule.php')], [
            [
                'PHPStan rule must extend Tooling\\PhpStan\\Rules\\Rule.',
                12,
            ],
        ]);
    }
}
