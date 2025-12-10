<?php

declare(strict_types=1);

namespace Tooling\Pint\Mcp\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Facades\Artisan;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Tool;

class Pint extends Tool
{
    protected string $name = 'tooling_pint';

    protected string $title = 'Run Pint code styler fixer';

    public function handle(Request $request): Response|ResponseFactory
    {
        $exitCode = \Illuminate\Support\Facades\Artisan::call(
            'tooling:pint', ['--format' => 'json', '--test' => $request->boolean('test', true)]
        );

        $output = Artisan::output();
        $decoded = json_decode(trim($output), true);

        return Response::structured(
            $decoded ?? ['raw_output' => $output, 'exit_code' => $exitCode]
        );
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'test' => $schema->boolean()
                ->title('test')
                ->description('Test for code style errors without fixing them')
                ->default(false),
        ];
    }
}
