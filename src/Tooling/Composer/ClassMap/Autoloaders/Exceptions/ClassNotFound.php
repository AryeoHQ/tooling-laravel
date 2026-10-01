<?php

declare(strict_types=1);

namespace Tooling\Composer\ClassMap\Autoloaders\Exceptions;

use RuntimeException;

class ClassNotFound extends RuntimeException
{
    public function __construct(string $class)
    {
        parent::__construct(
            sprintf('Class "%s" not found.', $class)
        );
    }
}
