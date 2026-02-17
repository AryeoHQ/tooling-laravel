<?php

declare(strict_types=1);

namespace Tests\Fixtures\Tooling;

enum EnumWithTraitAndInterface: string implements Contract
{
    use Concern;

    case Example = 'example';
}
