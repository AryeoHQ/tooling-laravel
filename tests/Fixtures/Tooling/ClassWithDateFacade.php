<?php

declare(strict_types=1);

namespace Tests\Fixtures\Tooling;

use Illuminate\Support\Facades\Date;

class ClassWithDateFacade
{
    public function handle(): void
    {
        $now = Date::now();
    }
}
