<?php

declare(strict_types=1);

namespace Tests\Fixtures\Tooling;

enum EnumWithInterface: string implements Contract
{
    case Example = 'example';
}
