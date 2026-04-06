<?php

declare(strict_types=1);

namespace Tooling\Rector;

use Illuminate\Support\Collection;
use Rector\Rector\AbstractRector;
use Tooling\Composer\Manifest;

final class Discovery
{
    protected Manifest $manifest { get => $this->manifest ??= resolve(Manifest::class); }

    /** @var Collection<array-key, class-string<AbstractRector>> */
    public Collection $rules { get => $this->rules ??= collect((array) $this->manifest->get('rector.rules')); }

    /** @var Collection<class-string<AbstractRector>, array<array-key, mixed>> */
    public Collection $configuredRules {
        get => $this->configuredRules ??= collect((array) $this->manifest->get('rector.configured_rules'))->filter(
            fn (mixed $result, int|string $rule) => is_string($rule) && is_a($rule, AbstractRector::class, true) && is_array($result)
        );
    }

    /** @var Collection<class-string<AbstractRector>, array<array-key, mixed>> */
    public Collection $skip {
        get => $this->skip ??= collect((array) $this->manifest->get('rector.skip'))->filter(
            fn (mixed $paths, int|string $rule) => is_string($rule) && is_a($rule, AbstractRector::class, true) && is_array($paths)
        );
    }
}
