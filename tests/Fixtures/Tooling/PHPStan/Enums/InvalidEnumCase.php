<?php

declare(strict_types=1);

namespace Tests\Fixtures\Tooling\PHPStan\Enums;

enum Status
{
    case INVALID_CASE;
    case snake_case;
}
