<?php

declare(strict_types=1);

namespace Tests\Fixtures\Tooling;

enum EnumWithChildInterface: string implements ChildContract
{
    case Example = 'example';
}
