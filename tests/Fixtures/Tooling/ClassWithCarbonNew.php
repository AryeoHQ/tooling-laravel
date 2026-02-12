<?php

declare(strict_types=1);

namespace Tests\Fixtures\Tooling;

use Carbon\Carbon;

class ClassWithCarbonNew
{
    public function handle(): void
    {
        $date = new Carbon('2024-01-01');
    }
}
