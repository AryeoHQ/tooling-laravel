<?php

declare(strict_types=1);

namespace Tooling\Rector\Rules;

use Tests\TestCase;
use Tests\Tooling\Concerns\GetsFixtures;
use Tooling\Rector\Rules\Provides\ManagesAttributes;
use Tooling\Rector\Rules\Provides\ManagesInterfaces;
use Tooling\Rector\Rules\Provides\ManagesMethods;
use Tooling\Rector\Rules\Provides\ManagesTraits;
use Tooling\Rector\Rules\Provides\ValidatesAttributes;
use Tooling\Rector\Rules\Provides\ValidatesInheritance;
use Tooling\Rector\Rules\Provides\ValidatesMethods;
use Tooling\Rector\Rules\Testing\Concerns\ManagesAttributesTestCases;
use Tooling\Rector\Rules\Testing\Concerns\ManagesInterfacesTestCases;
use Tooling\Rector\Rules\Testing\Concerns\ManagesMethodsTestCases;
use Tooling\Rector\Rules\Testing\Concerns\ManagesTraitsTestCases;
use Tooling\Rector\Rules\Testing\Concerns\ValidatesAttributesTestCases;
use Tooling\Rector\Rules\Testing\Concerns\ValidatesInheritanceTestCases;
use Tooling\Rector\Rules\Testing\Concerns\ValidatesMethodsTestCases;
use Tooling\Rector\Testing\ParsesNodes;
use Tooling\Rector\Testing\ParsesNodesWithScope;

class RuleTest extends TestCase
{
    use GetsFixtures;
    use ManagesAttributes;
    use ManagesAttributesTestCases;
    use ManagesInterfaces;
    use ManagesInterfacesTestCases;
    use ManagesMethods;
    use ManagesMethodsTestCases;
    use ManagesTraits;
    use ManagesTraitsTestCases;
    use ParsesNodes;
    use ParsesNodesWithScope;
    use ValidatesAttributes;
    use ValidatesAttributesTestCases;
    use ValidatesInheritance;
    use ValidatesInheritanceTestCases;
    use ValidatesMethods;
    use ValidatesMethodsTestCases;
}
