<?php

declare(strict_types=1);

namespace Tooling\PhpStan\Rules;

use Tests\TestCase;
use Tests\Tooling\Concerns\GetsFixtures;
use Tooling\PhpStan\Rules\Provides\ValidatesAttributes;
use Tooling\PhpStan\Rules\Provides\ValidatesInheritance;
use Tooling\PhpStan\Rules\Provides\ValidatesMethods;
use Tooling\PhpStan\Rules\Testing\Concerns\ValidatesAttributesTestCases;
use Tooling\PhpStan\Rules\Testing\Concerns\ValidatesInheritanceTestCases;
use Tooling\PhpStan\Rules\Testing\Concerns\ValidatesMethodsTestCases;
use Tooling\Rector\Testing\ParsesNodes;
use Tooling\Rector\Testing\ParsesNodesWithScope;

class RuleTest extends TestCase
{
    use GetsFixtures;
    use ParsesNodes;
    use ParsesNodesWithScope;
    use ValidatesAttributes;
    use ValidatesAttributesTestCases;
    use ValidatesInheritance;
    use ValidatesInheritanceTestCases;
    use ValidatesMethods;
    use ValidatesMethodsTestCases;
}
