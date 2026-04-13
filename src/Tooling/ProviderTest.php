<?php

declare(strict_types=1);

namespace Tooling;

use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ProviderTest extends TestCase
{
    #[Test]
    public function it_deep_merges_config_preserving_package_defaults(): void
    {
        config()->set('tooling', [
            'phpstan' => [
                'cli' => [
                    'arguments' => [
                        'paths' => ['app/Custom'],
                    ],
                ],
            ],
        ]);

        (new Provider($this->app))->register();

        $this->assertSame(['app/Custom'], config('tooling.phpstan.cli.arguments.paths'));

        $configuration = config('tooling.phpstan.cli.options.configuration');
        $this->assertIsString($configuration);
        $this->assertFileExists($configuration);
    }
}
