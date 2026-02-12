<?php

declare(strict_types=1);

namespace Tests\Fixtures\Tooling\PHPStan\Enums;

enum Status
{
    case Pending;
    case Approved;
    case Rejected;
}
