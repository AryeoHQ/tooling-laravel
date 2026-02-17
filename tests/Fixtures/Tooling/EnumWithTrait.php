<?php

declare(strict_types=1);

namespace Tests\Fixtures\Tooling;

enum EnumWithTrait: string
{
    use Concern;

    case Example = 'example';
}
