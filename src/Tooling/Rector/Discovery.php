<?php

declare(strict_types=1);

namespace Tooling\Rector;

use Illuminate\Support\Collection;
use Rector\Rector\AbstractRector;
use Tooling\Composer\Manifest;

final class Discovery
{
    protected Manifest $manifest { get => $this->manifest ??= new Manifest; }

    /** @var Collection<array-key, class-string<AbstractRector>> */
    public Collection $rules { get => $this->rules ??= collect((array) $this->manifest->get('rector.rules')); }

    /** @var Collection<class-string<AbstractRector>, array<array-key, mixed>> */
    public Collection $configuredRules {
        get => $this->configuredRules ??= collect((array) $this->manifest->get('rector.configured_rules'))->filter(
            fn (mixed $result, string $rule) => is_a($rule, AbstractRector::class, true) && is_array($result)
        );
    }
}
