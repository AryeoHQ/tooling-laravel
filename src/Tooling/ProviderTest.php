<?php

declare(strict_types=1);

namespace Tooling;

use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ProviderTest extends TestCase
{
    #[Test]
    public function it_registers_tooling_laravel_config_defaults(): void
    {
        (new Provider($this->app))->register();

        $this->assertFalse(config('tooling-laravel.rector.with_import_names'));
    }

    #[Test]
    public function it_allows_app_to_override_tooling_laravel_config(): void
    {
        config()->set('tooling-laravel', [
            'rector' => [
                'with_import_names' => true,
            ],
        ]);

        (new Provider($this->app))->register();

        $this->assertTrue(config('tooling-laravel.rector.with_import_names'));
    }
}
