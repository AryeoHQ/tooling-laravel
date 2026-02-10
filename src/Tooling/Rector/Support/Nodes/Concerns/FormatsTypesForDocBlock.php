<?php

declare(strict_types=1);

namespace Tooling\Rector\Support\Nodes\Concerns;

trait FormatsTypesForDocBlock
{
    /** @var array<int, string> */
    private array $builtInTypes = [
        'string',
        'int',
        'float',
        'bool',
        'array',
        'callable',
        'iterable',
        'object',
        'mixed',
        'void',
        'null',
        'never',
        'false',
        'true',
        'resource',
        'self',
        'parent',
        'static',
    ];

    private function formatTypeForDocBlock(string $type): string
    {
        $cleanType = ltrim($type, '\\');

        // If it's a built-in type, return as-is. Otherwise, add a leading backslash.
        return in_array($cleanType, $this->builtInTypes, true) ? $cleanType : "\\{$cleanType}";
    }
}
