<?php

declare(strict_types=1);

namespace Tooling\Nodes\Concerns;

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

        return $this->isBuiltInType($cleanType) ? $cleanType : "\\{$cleanType}";
    }

    private function isBuiltInType(string $type): bool
    {
        return in_array(ltrim($type, '\\'), $this->builtInTypes, true);
    }
}
