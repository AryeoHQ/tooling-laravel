<?php

declare(strict_types=1);

namespace Tests\Fixtures\Tooling\PhpStan\Carbon;

use Illuminate\Support\Facades\Date;

class ValidCarbonUsage
{
    public function doSomething(): void
    {
        $now = Date::now();
        $today = Date::today();
    }
}
