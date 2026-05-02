<?php

declare(strict_types=1);

namespace Tooling\Console\Testing\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class ExpectsArguments implements ConfirmsArguments {}
