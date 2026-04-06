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
    function (RectorConfigBuilder $builder) use ($discovery) {
        $discovery->configuredRules->each(
            fn (array $config, string $rule) => $builder->withConfiguredRule($rule, $config)
        );

        $builder->withSkip($discovery->skip->toArray());
    }
);
