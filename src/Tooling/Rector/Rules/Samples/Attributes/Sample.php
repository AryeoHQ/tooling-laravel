<?php

declare(strict_types=1);

namespace Tooling\Rector\Rules\Samples\Attributes;

use Attribute;
use Illuminate\Support\Str;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Tooling\Rector\Rules\Rule;
use Tooling\Rector\Rules\Samples\Attributes\Exceptions\ViewNotFound;

#[Attribute(Attribute::TARGET_CLASS)]
class Sample
{
    public readonly string $namespace;

    /** @var Rule<\PhpParser\Node> */
    public readonly Rule $for;

    public string $name {
        get => Str::of(class_basename($this->for))->snake('-')->toString();
    }

    public function __construct(string $namespace)
    {
        $this->namespace = $namespace;
    }

    /** @param Rule<\PhpParser\Node> $rule */
    public function for(Rule $rule): static
    {
        $this->for = $rule;

        return $this;
    }

    private function render(string $phase): string
    {
        $path = "{$this->namespace}::{$this->name}.{$phase}";

        throw_unless(view()->exists($path), ViewNotFound::class, $path);

        return view($path)->render();
    }

    public function toCodeSample(): CodeSample
    {
        return new CodeSample(
            $this->render('before'),
            $this->render('after')
        );
    }
}
