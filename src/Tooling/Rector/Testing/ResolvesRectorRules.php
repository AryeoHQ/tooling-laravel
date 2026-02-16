<?php

declare(strict_types=1);

namespace Tooling\Rector\Testing;

use Rector\Config\RectorConfig;
use Rector\DependencyInjection\LazyContainerFactory;
use Tooling\Rector\Rules\Rule;

trait ResolvesRectorRules
{
    private null|RectorConfig $rectorConfig = null;

    protected function setUpResolvesRectorRules(): void
    {
        $this->rectorConfig = (new LazyContainerFactory)->create();
    }

    /**
     * @template T of Rule
     *
     * @param  class-string<T>  $class
     * @return T
     */
    protected function resolveRule(string $class): Rule
    {
        if ($this->rectorConfig === null) {
            $this->setUpResolvesRectorRules();
        }

        return $this->rectorConfig->make($class);
    }
}
