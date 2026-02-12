<?php

declare(strict_types=1);

namespace Tests\Fixtures\Tooling\PHPStan\Carbon;

use Carbon\Carbon;

class InvalidCarbonUsage
{
    public function doSomething(): void
    {
        $now = Carbon::now();
        $instance = new Carbon;
    }
}
