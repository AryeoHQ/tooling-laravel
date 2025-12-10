<?php

declare(strict_types=1);

namespace Tooling\PHPStan\Mcp\Tools;

use Illuminate\Support\Facades\Artisan;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Tool;

class PhpStan extends Tool
{
    protected string $name = 'tooling_phpstan';

    protected string $title = 'Run PHPStan Static Analysis';

    public function handle(Request $request): Response|ResponseFactory
    {
        $exitCode = Artisan::call(
            'tooling:phpstan', ['--error-format' => 'json', '--no-progress' => true]
        );

        $output = Artisan::output();
        $decoded = json_decode(trim($output), true);

        return Response::structured(
            $decoded ?? ['raw_output' => $output, 'exit_code' => $exitCode]
        );
    }
}
