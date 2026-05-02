<?php

declare(strict_types=1);

namespace Mcp\Servers;

class Development extends Server
{
    protected string $name = 'Development';

    protected string $version = '0.0.1';

    protected string $instructions = 'This server provides tooling for local development purposes.';
}
