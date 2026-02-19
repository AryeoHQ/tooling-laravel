<?php

declare(strict_types=1);

namespace Tests\Fixtures\Tooling;

enum EnumWithMethod: string
{
    case Example = 'example';

    public function enumMethod(): void {}
}
