<?php

declare(strict_types=1);

namespace Tests\Tooling\Rector\Rules;

use Tests\TestCase;
use Tests\Tooling\Concerns\GetsFixtures;
use Tests\Tooling\Rector\Rules\Provides\ManagesAttributesCases;
use Tests\Tooling\Rector\Rules\Provides\ManagesInterfacesCases;
use Tests\Tooling\Rector\Rules\Provides\ManagesMethodsCases;
use Tests\Tooling\Rector\Rules\Provides\ManagesTraitsCases;
use Tests\Tooling\Rector\Rules\Provides\ValidatesAttributesCases;
use Tests\Tooling\Rector\Rules\Provides\ValidatesInheritanceCases;
use Tests\Tooling\Rector\Rules\Provides\ValidatesMethodsCases;
use Tooling\Rector\Rules\Provides\ManagesAttributes;
use Tooling\Rector\Rules\Provides\ManagesInterfaces;
use Tooling\Rector\Rules\Provides\ManagesMethods;
use Tooling\Rector\Rules\Provides\ManagesTraits;
use Tooling\Rector\Rules\Provides\ValidatesAttributes;
use Tooling\Rector\Rules\Provides\ValidatesInheritance;
use Tooling\Rector\Rules\Provides\ValidatesMethods;
use Tooling\Rector\Testing\ParsesNodes;
use Tooling\Rector\Testing\ParsesNodesWithScope;

class RuleTest extends TestCase
{
    use GetsFixtures;
    use ManagesAttributes;
    use ManagesAttributesCases;
    use ManagesInterfaces;
    use ManagesInterfacesCases;
    use ManagesMethods;
    use ManagesMethodsCases;
    use ManagesTraits;
    use ManagesTraitsCases;
    use ParsesNodes;
    use ParsesNodesWithScope;
    use ValidatesAttributes;
    use ValidatesAttributesCases;
    use ValidatesInheritance;
    use ValidatesInheritanceCases;
    use ValidatesMethods;
    use ValidatesMethodsCases;
}
