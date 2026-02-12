<?php

declare(strict_types=1);

namespace Tests\Fixtures\Tooling\PhpStan\Enums;

enum Status
{
    case Pending;
    case Approved;
    case Rejected;
}
