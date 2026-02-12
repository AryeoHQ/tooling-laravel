<?php

declare(strict_types=1);

namespace Tests\Tooling\PhpStan\Rules\Enums;

use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use PHPUnit\Framework\Attributes\Test;
use Tests\Tooling\Concerns\GetsFixtures;
use Tooling\PhpStan\Rules\Enums\CaseMustBePascal;

/**
 * @extends RuleTestCase<CaseMustBePascal>
 */
class CaseMustBePascalTest extends RuleTestCase
{
    use GetsFixtures;

    protected function getRule(): Rule
    {
        return new CaseMustBePascal;
    }

    #[Test]
    public function it_passes_when_enum_case_is_pascal_case(): void
    {
        $this->analyse([$this->getFixturePath('PhpStan/Enums/ValidEnumCase.php')], []);
    }

    #[Test]
    public function it_fails_when_enum_case_is_not_pascal_case(): void
    {
        $this->analyse([$this->getFixturePath('PhpStan/Enums/InvalidEnumCase.php')], [
            [
                'Enum case must be `PascalCase`.',
                9,
            ],
            [
                'Enum case must be `PascalCase`.',
                10,
            ],
        ]);
    }
}
