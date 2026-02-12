<?php

declare(strict_types=1);

namespace Tests\Tooling\PhpStan\Rules\Rector;

use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use PHPUnit\Framework\Attributes\Test;
use Tests\Tooling\Concerns\GetsFixtures;
use Tooling\PhpStan\Rules\Rector\RuleMustExtendRule;

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

    #[RunInSeparateProcess]
    #[Test]
    public function it_passes_when_rule_extends_base_rule(): void
    {
        $this->analyse([$this->getFixturePath('PhpStan/Rector/ValidRule.php')], []);
    }

    #[RunInSeparateProcess]
    #[Test]
    public function it_fails_when_rule_directly_extends_abstract_rector(): void
    {
        $this->analyse([$this->getFixturePath('PhpStan/Rector/InvalidRule.php')], [
            [
                'Rector rule must extend Tooling\\Rector\\Rules\\Rule.',
                13,
            ],
        ]);
    }
}
