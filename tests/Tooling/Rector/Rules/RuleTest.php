<?php

declare(strict_types=1);

namespace Tests\Tooling\Rector\Rules;

use Tests\TestCase;
use Tests\Tooling\Concerns\GetsFixtures;
use Tests\Tooling\Rector\Rules\Concerns\ValidatesInheritanceCases;
use Tests\Tooling\Rector\Rules\Concerns\ValidatesMethodsCases;
use Tooling\Rector\Rules\Provides\ValidatesInheritance;
use Tooling\Rector\Rules\Provides\ValidatesMethods;
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
