<?php

declare(strict_types=1);

namespace Tooling\Rector\Rules\Definitions\Attributes;

use Attribute;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

#[Attribute(Attribute::TARGET_CLASS)]
class Definition
{
    public readonly string $description;

    public function __construct(string $description)
    {
        $this->description = $description;
    }

    public function toRuleDefinition(CodeSample $codeSample): RuleDefinition
    {
        return new RuleDefinition($this->description, [$codeSample]);
    }
}
