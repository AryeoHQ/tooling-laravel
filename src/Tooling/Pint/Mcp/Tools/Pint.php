<?php

declare(strict_types=1);

namespace Tooling\Pint\Mcp\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Facades\Artisan;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Title;
use Laravel\Mcp\Server\Tool;

#[Name('tooling_pint')]
#[Title('Run Pint code styler fixer')]
class Pint extends Tool
{
    public function handle(Request $request): Response|ResponseFactory
    {
        $exitCode = \Illuminate\Support\Facades\Artisan::call(
            'tooling:pint', ['--format' => 'json', '--test' => $request->boolean('test', false)]
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
