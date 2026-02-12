<?php

declare(strict_types=1);

namespace Tests\Fixtures\Tooling;

use Carbon\CarbonImmutable;

class ClassWithCarbonImmutableStaticCall
{
    public function handle(): void
    {
        $now = CarbonImmutable::now();
    }
}
