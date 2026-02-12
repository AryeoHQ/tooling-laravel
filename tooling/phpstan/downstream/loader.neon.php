<?php

use Tooling\PhpStan\Discovery;

$discovery = new Discovery;

return $discovery->includes->isNotEmpty() ? ['includes' => $discovery->includes->toArray()] : [];
