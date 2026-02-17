<?php

declare(strict_types=1);

namespace Tests\Tooling\PhpStan\Rules;

use Tests\TestCase;
use Tests\Tooling\Concerns\GetsFixtures;
use Tests\Tooling\PhpStan\Rules\Concerns\ValidatesInheritanceCases;
use Tests\Tooling\PhpStan\Rules\Concerns\ValidatesMethodsCases;
use Tooling\PhpStan\Rules\Provides\ValidatesInheritance;
use Tooling\PhpStan\Rules\Provides\ValidatesMethods;
use Tooling\Rector\Testing\ParsesNodes;
use Tooling\Rector\Testing\ParsesNodesWithScope;

class RuleTest extends TestCase
{
    use GetsFixtures;
    use ParsesNodes;
    use ParsesNodesWithScope;
    use ValidatesInheritance;
    use ValidatesInheritanceCases;
    use ValidatesMethods;
    use ValidatesMethodsCases;
}
