<?php

declare(strict_types=1);

namespace Tests\Tooling\Concerns;

trait GetsFixtures
{
    protected function getFixturePath(string $filename): string
    {
        return __DIR__.'/../../Fixtures/Tooling/'.$filename;
    }
}
