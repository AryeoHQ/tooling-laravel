<?php

declare(strict_types=1);

namespace Tooling\Rector\Rules\Samples\Attributes\Exceptions;

use RuntimeException;

class ViewNotFound extends RuntimeException
{
    public function __construct(string $path)
    {
        parent::__construct(
            sprintf('`%s` view not found.', $path)
        );
    }
}
