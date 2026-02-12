<?php

declare(strict_types=1);

namespace Tooling\Rules\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS | Attribute::IS_REPEATABLE)]
final class NodeType
{
    /** @var class-string<\PhpParser\Node> */
    public readonly string $class;

    /**
     * @param  class-string<\PhpParser\Node>  $class
     */
    public function __construct(string $class)
    {
        $this->class = $class;
    }
}
