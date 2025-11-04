<?php

use Tooling\PHPStan\Discovery;

$discovery = new Discovery;

return $discovery->includes->isNotEmpty() ? ['includes' => $discovery->includes->toArray()] : [];
