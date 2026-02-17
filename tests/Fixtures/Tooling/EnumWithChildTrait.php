<?php

declare(strict_types=1);

namespace Tests\Fixtures\Tooling;

enum EnumWithChildTrait: string
{
    use ChildConcern;

    case Example = 'example';
}
