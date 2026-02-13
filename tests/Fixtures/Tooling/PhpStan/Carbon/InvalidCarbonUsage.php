<?php

declare(strict_types=1);

namespace Tests\Fixtures\Tooling\PhpStan\Carbon;

use Carbon\Carbon;

class InvalidCarbonUsage
{
    public function doSomething(): void
    {
        $now = Carbon::now();
        $instance = new Carbon;
    }
}
