<?php

declare(strict_types=1);

namespace Tooling\Rules\Attributes\Exceptions;

use RuntimeException;

class NodeTypeMissing extends RuntimeException
{
    public function __construct(object $rule)
    {
        parent::__construct(
            sprintf('`%s` must define at least one `Node` attribute.', class_basename($rule))
        );
    }
}
