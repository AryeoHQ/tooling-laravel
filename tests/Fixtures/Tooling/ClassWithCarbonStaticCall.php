<?php

declare(strict_types=1);

namespace Tests\Fixtures\Tooling;

use Carbon\Carbon;

class ClassWithCarbonStaticCall
{
    public function handle(): void
    {
        $now = Carbon::now();
    }
}
