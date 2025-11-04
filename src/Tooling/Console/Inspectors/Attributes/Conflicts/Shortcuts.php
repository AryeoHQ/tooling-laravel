<?php

declare(strict_types=1);

namespace Tooling\Console\Inspectors\Attributes\Conflicts;

use Attribute;
use Illuminate\Support\Collection;

#[Attribute(Attribute::TARGET_CLASS)]
class Shortcuts
{
    /** @var Collection<array-key, mixed> */
    public Collection $list;

    /**
     * @param  iterable<array-key, mixed>  $list
     */
    public function __construct(iterable $list = [])
    {
        $this->list = collect($list);
    }
}
