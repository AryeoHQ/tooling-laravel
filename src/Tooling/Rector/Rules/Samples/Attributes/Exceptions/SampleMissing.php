<?php

declare(strict_types=1);

namespace Tooling\Rector\Rules\Samples\Attributes\Exceptions;

use RuntimeException;
use Tooling\Rector\Rules\Rule;

class SampleMissing extends RuntimeException
{
    public function __construct(Rule $rule)
    {
        parent::__construct(
            sprintf('`%s` must use the `Sample` attribute.', class_basename($rule))
        );
    }
}
