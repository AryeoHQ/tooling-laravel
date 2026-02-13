<?php

declare(strict_types=1);

namespace Tooling\Rector\Rules\Definitions\Attributes\Exceptions;

use RuntimeException;
use Tooling\Rector\Rules\Rule;

class DefinitionMissing extends RuntimeException
{
    /** @param Rule<\PhpParser\Node> $rule */
    public function __construct(Rule $rule)
    {
        parent::__construct(
            sprintf('`%s` must use the `Definition` attribute.', class_basename($rule))
        );
    }
}
