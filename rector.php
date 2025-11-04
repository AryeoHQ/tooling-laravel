<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Configuration\RectorConfigBuilder;
use Tooling\Rector\Discovery;

$discovery = new Discovery;

$builder = RectorConfig::configure()->withRules(
    $discovery->rules->toArray()
);

return tap(
    $builder,
    fn (RectorConfigBuilder $builder) => $discovery->configuredRules->each(
        fn (array $config, string $rule) => $builder->withConfiguredRule($rule, $config)
    )
);
