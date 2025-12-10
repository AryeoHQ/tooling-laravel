<?php

declare(strict_types=1);

namespace Tooling\Rector\Mcp\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Facades\Artisan;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Tool;

class Rector extends Tool
{
    protected string $name = 'tooling_rector';

    protected string $title = 'Run Rector code styler fixer';

    public function handle(Request $request): Response|ResponseFactory
    {
        $exitCode = Artisan::call(
            'tooling:rector', ['--output-format' => 'json', '--dry-run' => $request->boolean('dry', true)]
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
            'dry' => $schema->boolean()
                ->title('dry')
                ->description('Only see the diff of changes, do not save them to files')
                ->default(false),
        ];
    }
}
