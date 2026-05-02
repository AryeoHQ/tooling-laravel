<?php

declare(strict_types=1);

namespace Tooling\Mcp\Resources;

use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Resource;
use Laravel\Mcp\Support\UriTemplate;

class Readme extends Resource
{
    protected string $name = 'tooling_readme';

    protected string $title = 'Readme for Laravel Tooling';

    public function uriTemplate(): UriTemplate
    {
        return new UriTemplate('file://tooling-laravel/README.md');
    }

    public function handle(): Response
    {
        return Response::text(
            file_get_contents(__DIR__.'/../../../../README.md')
        );
    }
}
